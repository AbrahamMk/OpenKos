<?php

namespace App\Actions\Invoices;

use App\Actions\Utility\BillUtilityReadings;
use App\Enums\BillingUnit;
use App\Enums\InvoiceStatus;
use App\Events\Invoice\InvoiceGenerated;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Lease;
use App\Services\Payments\MoneyConverter;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateInvoices
{
    private const MAX_UNIQUE_RETRIES = 3;

    /** Minimum calendar-month look-ahead; must stay >= reminder lookahead. */
    private const MIN_HORIZON_MONTHS = 2;

    public function __construct(
        private MoneyConverter $money,
        private BillUtilityReadings $billUtilityReadings,
    ) {}

    /**
     * Materialize invoices for upcoming billing periods.
     *
     * Horizon is at least 2 months ahead (reminder invariant), and for
     * multi-month/year billing it expands to one full billing interval so a
     * mid-period start that skips to the next cycle still gets an invoice.
     */
    public function execute(?Lease $lease = null): int
    {
        // ponytail: retry only known invoice-key races; sustained contention or
        // unrelated constraint errors must remain visible to the caller.
        for ($attempt = 1; $attempt <= self::MAX_UNIQUE_RETRIES; $attempt++) {
            try {
                return DB::transaction(fn (): int => $this->generateBatch($lease));
            } catch (QueryException $exception) {
                if ($attempt === self::MAX_UNIQUE_RETRIES || ! $this->isRetryableInvoiceUniqueViolation($exception)) {
                    throw $exception;
                }
            }
        }

        return 0;
    }

    private function generateBatch(?Lease $lease): int
    {
        // ponytail: lock the selected lease set once; per-lease locking can
        // replace this if concurrent generation becomes a measured bottleneck.
        $leases = $lease
            ? Lease::query()->whereKey($lease->getKey())->lockForUpdate()->get()
            : Lease::active()->lockForUpdate()->get();

        if ($leases->isEmpty()) {
            return 0;
        }

        $candidates = [];
        $horizonByLease = [];

        foreach ($leases as $lockedLease) {
            $horizon = $this->generationHorizon($lockedLease);
            $horizonByLease[$lockedLease->getKey()] = $horizon;

            foreach ($lockedLease->schedule() as $period) {
                if ($period->period_start->gt($horizon)) {
                    continue;
                }

                $currency = $lockedLease->currency;

                $candidates[] = [
                    'lease_id' => $lockedLease->getKey(),
                    'period_start' => $period->period_start,
                    'period_end' => $period->period_end,
                    'due_date' => $period->due_date,
                    'amount' => $this->money->normalizeAmount((string) $period->amount, $currency),
                    'currency' => $currency,
                ];
            }
        }

        if ($candidates === []) {
            return 0;
        }

        $leaseIds = $leases->modelKeys();
        $minimumPeriod = collect($candidates)->min('period_start')->toDateString();
        $maximumPeriod = collect($horizonByLease)
            ->max(fn (CarbonInterface $horizon): string => $horizon->toDateString());
        $existingKeys = Invoice::query()
            ->whereIn('lease_id', $leaseIds)
            ->whereBetween('period_start', [$minimumPeriod, $maximumPeriod])
            ->get(['id', 'lease_id', 'period_start'])
            ->keyBy(fn (Invoice $invoice): string => $this->periodKey($invoice->lease_id, $invoice->period_start));

        $newCandidates = collect($candidates)
            ->reject(fn (array $candidate): bool => $existingKeys->has(
                $this->periodKey($candidate['lease_id'], $candidate['period_start'])
            ))
            ->values();

        $createdInvoices = collect();

        if ($newCandidates->isNotEmpty()) {
            $references = Invoice::nextReferences($newCandidates->count());
            $timestamp = now();
            $invoiceRows = $newCandidates->map(fn (array $candidate, int $index): array => [
                'lease_id' => $candidate['lease_id'],
                'reference' => $references[$index],
                'period_start' => $candidate['period_start']->toDateString(),
                'period_end' => $candidate['period_end']->toDateString(),
                'due_date' => $candidate['due_date']->toDateString(),
                'status' => InvoiceStatus::Pending->value,
                'total' => $candidate['amount'],
                'amount_paid' => 0,
                'currency' => $candidate['currency'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->all();

            Invoice::query()->insert($invoiceRows);

            $candidateKeys = $newCandidates->mapWithKeys(fn (array $candidate): array => [
                $this->periodKey($candidate['lease_id'], $candidate['period_start']) => $candidate,
            ]);
            $persistedInvoices = Invoice::query()
                ->whereIn('lease_id', $newCandidates->pluck('lease_id')->unique()->values())
                ->whereBetween('period_start', [$minimumPeriod, $maximumPeriod])
                ->get()
                ->keyBy(fn (Invoice $invoice): string => $this->periodKey($invoice->lease_id, $invoice->period_start));

            $createdInvoices = $newCandidates
                ->map(fn (array $candidate): ?Invoice => $persistedInvoices->get(
                    $this->periodKey($candidate['lease_id'], $candidate['period_start'])
                ))
                ->filter()
                ->values();

            if ($createdInvoices->count() !== $newCandidates->count()) {
                throw new \LogicException('Invoice batch did not persist all generated periods.');
            }

            InvoiceLineItem::query()->insert($createdInvoices->map(function (Invoice $invoice) use ($candidateKeys, $timestamp): array {
                $candidate = $candidateKeys->get($this->periodKey($invoice->lease_id, $invoice->period_start));

                return [
                    'invoice_id' => $invoice->getKey(),
                    'type' => 'rent',
                    'description' => $this->rentLineDescription(
                        $invoice->period_start,
                        $candidate['period_end'],
                    ),
                    'amount' => $candidate['amount'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })->all());
        }

        $invoicesToBill = $existingKeys->values()
            ->merge($createdInvoices)
            ->unique('id')
            ->values();

        foreach ($invoicesToBill as $invoice) {
            $this->billUtilityReadings->execute($invoice);
        }

        $createdInvoices->each->refresh();

        if ($createdInvoices->isEmpty()) {
            return 0;
        }

        foreach ($createdInvoices as $invoice) {
            $invoice->recordAudit('create');
        }

        DB::afterCommit(function () use ($createdInvoices): void {
            foreach ($createdInvoices as $invoice) {
                InvoiceGenerated::dispatch($invoice);
            }
        });

        return $createdInvoices->count();
    }

    private function periodKey(int $leaseId, CarbonInterface $periodStart): string
    {
        return $leaseId.'|'.$periodStart->toDateString();
    }

    /**
     * Look far enough ahead that the next billing cycle is always included,
     * even when a mid-month start skips the current cycle by a full interval.
     */
    private function generationHorizon(Lease $lease): CarbonInterface
    {
        $interval = max(1, (int) ($lease->billing_interval ?? 1));
        $unit = $lease->billing_unit instanceof BillingUnit
            ? $lease->billing_unit
            : BillingUnit::tryFrom((string) $lease->billing_unit) ?? BillingUnit::Month;

        $intervalMonths = match ($unit) {
            BillingUnit::Year => $interval * 12,
            BillingUnit::Month => $interval,
            default => self::MIN_HORIZON_MONTHS,
        };

        return now()
            ->addMonthsNoOverflow(max(self::MIN_HORIZON_MONTHS, $intervalMonths))
            ->endOfMonth();
    }

    private function rentLineDescription(CarbonInterface $periodStart, CarbonInterface $periodEnd): string
    {
        if ($periodStart->isSameMonth($periodEnd)) {
            return 'Rent '.$periodStart->format('F Y');
        }

        return 'Rent '.$periodStart->format('M Y').' – '.$periodEnd->format('M Y');
    }

    private function isRetryableInvoiceUniqueViolation(QueryException $exception): bool
    {
        $errorInfo = $exception->errorInfo ?? [];
        $isUniqueViolation = match (DB::getDriverName()) {
            'pgsql' => (string) ($errorInfo[0] ?? $exception->getCode()) === '23505',
            'mysql' => (int) ($errorInfo[1] ?? 0) === 1062,
            'sqlite' => (int) ($errorInfo[1] ?? 0) === 19,
            default => false,
        };

        if (! $isUniqueViolation) {
            return false;
        }

        $message = Str::lower($exception->getMessage());

        return str_contains($message, 'invoices_lease_id_period_start_unique')
            || str_contains($message, 'invoices.lease_id, invoices.period_start')
            || str_contains($message, 'invoices_reference_unique')
            || str_contains($message, 'invoices.reference')
            || str_contains($message, 'invoice_line_items_utility_reading_id_unique');
    }
}

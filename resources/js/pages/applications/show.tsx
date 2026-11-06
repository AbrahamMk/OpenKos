import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { InputError, StatusBadge } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    formatBillingOptionLabel,
    formatDate,
    formatDateTime,
    formatPrice,
} from '@/lib/formatters';
import { t } from '@/lib/i18n';
import { convert, index, transition } from '@/routes/applications';
import type {
    ApplicationActionErrors,
    ApplicationShowProps,
    ApplicationTransitionFormData,
    ApplicationWithdrawalFormData,
    OperatorApplication,
} from '@/types';

function DetailRow({
    label,
    value,
}: {
    label: string;
    value: ReactNode;
}) {
    if (!value) {
        return null;
    }

    return (
        <div className="grid gap-1 sm:grid-cols-[10rem_1fr] sm:gap-4">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="text-sm font-medium whitespace-pre-wrap">{value}</dd>
        </div>
    );
}

export default function ApplicationShow({
    application,
    operator,
}: ApplicationShowProps) {
    const detail = application as OperatorApplication;
    const canWithdraw =
        !operator && ['new', 'reviewing'].includes(application.status);
    const transitionForm = useForm<ApplicationTransitionFormData>({
        status: application.status,
        operator_notes: detail.operator_notes ?? '',
        applicant_feedback: detail.applicant_feedback ?? '',
    });
    const conversionForm = useForm<Record<string, never>>({});
    const withdrawalForm = useForm<ApplicationWithdrawalFormData>({
        status: 'withdrawn',
    });

    const offerLabel =
        application.rental_amount &&
        application.rental_currency &&
        application.rental_billing_unit &&
        application.rental_billing_interval
            ? `${formatPrice(application.rental_amount, application.rental_currency)} · ${formatBillingOptionLabel(
                  application.rental_billing_interval,
                  application.rental_billing_unit,
              )}`
            : null;

    function submitTransition(event: FormEvent) {
        event.preventDefault();
        transitionForm.submit(transition(application.id), {
            preserveScroll: true,
        });
    }

    function submitConversion(event: FormEvent) {
        event.preventDefault();
        conversionForm.submit(convert(application.id), {
            preserveScroll: true,
        });
    }

    function submitWithdrawal(event: FormEvent) {
        event.preventDefault();
        withdrawalForm.submit(transition(application.id), {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={t('Application')} />
            <div className="mx-auto w-full max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <Link
                    href={index()}
                    className="text-sm text-muted-foreground underline-offset-4 hover:underline"
                >
                    {t('Back to applications')}
                </Link>
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-semibold">
                            {application.unit_type?.name ??
                                application.property?.name}
                        </h1>
                        <p className="text-muted-foreground">
                            {application.property?.name &&
                            application.target_type === 'unit_type'
                                ? `${application.property.name} · `
                                : null}
                            {application.target_type === 'unit_type'
                                ? t('Unit type')
                                : t('Whole property')}
                        </p>
                    </div>
                    <StatusBadge
                        domain="application"
                        value={application.status}
                    />
                </div>

                <section className="grid gap-4 rounded-xl border bg-card p-5">
                    <h2 className="font-semibold">{t('Listing & offer')}</h2>
                    <dl className="grid gap-3">
                        <DetailRow
                            label={t('Property')}
                            value={application.property?.name}
                        />
                        <DetailRow
                            label={t('Unit type')}
                            value={application.unit_type?.name}
                        />
                        <DetailRow label={t('Offer')} value={offerLabel} />
                        <DetailRow
                            label={t('Preferred move-in')}
                            value={
                                application.intended_move_in_date
                                    ? formatDate(
                                          application.intended_move_in_date,
                                      )
                                    : null
                            }
                        />
                        <DetailRow
                            label={t('Submitted')}
                            value={
                                application.submitted_at
                                    ? formatDateTime(application.submitted_at)
                                    : null
                            }
                        />
                    </dl>
                </section>

                {operator && (
                    <section className="grid gap-4 rounded-xl border bg-card p-5">
                        <h2 className="font-semibold">
                            {t('Applicant information')}
                        </h2>
                        <dl className="grid gap-3">
                            <DetailRow
                                label={t('Name')}
                                value={detail.applicant.name}
                            />
                            <DetailRow
                                label={t('Email')}
                                value={detail.applicant.email}
                            />
                            <DetailRow
                                label={t('Phone')}
                                value={detail.applicant.phone}
                            />
                        </dl>
                    </section>
                )}

                {(application.applicant_message || operator) && (
                    <section className="grid gap-4 rounded-xl border bg-card p-5">
                        <h2 className="font-semibold">
                            {t('Applicant message')}
                        </h2>
                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                            {application.applicant_message || t('No message provided.')}
                        </p>
                    </section>
                )}

                {application.applicant_feedback && !operator && (
                    <section className="rounded-xl border bg-card p-5">
                        <h2 className="font-semibold">
                            {t('Message from the property team')}
                        </h2>
                        <p className="mt-2 text-sm whitespace-pre-wrap text-muted-foreground">
                            {application.applicant_feedback}
                        </p>
                    </section>
                )}

                {operator && (
                    <section className="grid gap-4 rounded-xl border bg-card p-5">
                        <div>
                            <h2 className="font-semibold">
                                {t('Review decision')}
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {t(
                                    'Review the offer and applicant details above, then update the application status.',
                                )}
                            </p>
                        </div>
                        <form
                            onSubmit={submitTransition}
                            className="grid gap-4"
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="status">{t('Status')}</Label>
                                <select
                                    id="status"
                                    name="status"
                                    value={transitionForm.data.status}
                                    onChange={(event) =>
                                        transitionForm.setData(
                                            'status',
                                            event.target
                                                .value as ApplicationTransitionFormData['status'],
                                        )
                                    }
                                    className="h-10 rounded-md border bg-background px-3"
                                    disabled={transitionForm.processing}
                                >
                                    <option value="new" disabled>
                                        {t('New')}
                                    </option>
                                    <option value="reviewing">
                                        {t('Reviewing')}
                                    </option>
                                    <option value="accepted">
                                        {t('Accepted')}
                                    </option>
                                    <option value="rejected">
                                        {t('Rejected')}
                                    </option>
                                    <option value="withdrawn">
                                        {t('Withdrawn')}
                                    </option>
                                </select>
                                <InputError
                                    message={transitionForm.errors.status}
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="operator_notes">
                                    {t('Private operator notes')}
                                </Label>
                                <Textarea
                                    id="operator_notes"
                                    name="operator_notes"
                                    value={transitionForm.data.operator_notes}
                                    onChange={(event) =>
                                        transitionForm.setData(
                                            'operator_notes',
                                            event.target.value,
                                        )
                                    }
                                    placeholder={t(
                                        'Internal notes for the management team',
                                    )}
                                    disabled={transitionForm.processing}
                                />
                                <InputError
                                    message={
                                        transitionForm.errors.operator_notes
                                    }
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="applicant_feedback">
                                    {t('Applicant-facing feedback')}
                                </Label>
                                <Textarea
                                    id="applicant_feedback"
                                    name="applicant_feedback"
                                    value={
                                        transitionForm.data.applicant_feedback
                                    }
                                    onChange={(event) =>
                                        transitionForm.setData(
                                            'applicant_feedback',
                                            event.target.value,
                                        )
                                    }
                                    placeholder={t(
                                        'Optional message the applicant can see',
                                    )}
                                    disabled={transitionForm.processing}
                                />
                                <InputError
                                    message={
                                        transitionForm.errors
                                            .applicant_feedback
                                    }
                                />
                            </div>
                            <InputError
                                message={
                                    (
                                        transitionForm.errors as ApplicationActionErrors
                                    ).application
                                }
                            />
                            <Button
                                type="submit"
                                disabled={transitionForm.processing}
                            >
                                {t('Save application')}
                            </Button>
                        </form>
                        {detail.status === 'accepted' &&
                            !detail.converted_tenant_id && (
                                <form
                                    onSubmit={submitConversion}
                                    className="border-t pt-4"
                                >
                                    <p className="mb-3 text-sm text-muted-foreground">
                                        {t(
                                            'Accepting is not enough — convert the accepted applicant into a tenant record when you are ready.',
                                        )}
                                    </p>
                                    <InputError
                                        message={
                                            (
                                                conversionForm.errors as ApplicationActionErrors
                                            ).application
                                        }
                                    />
                                    <Button
                                        type="submit"
                                        variant="secondary"
                                        disabled={conversionForm.processing}
                                    >
                                        {t('Convert to Tenant')}
                                    </Button>
                                </form>
                            )}
                    </section>
                )}

                {canWithdraw && (
                    <form onSubmit={submitWithdrawal}>
                        <InputError
                            message={
                                (
                                    withdrawalForm.errors as ApplicationActionErrors
                                ).application
                            }
                        />
                        <Button
                            type="submit"
                            variant="outline"
                            disabled={withdrawalForm.processing}
                        >
                            {t('Withdraw application')}
                        </Button>
                    </form>
                )}
            </div>
        </>
    );
}

ApplicationShow.layout = {
    breadcrumbs: [
        {
            title: 'Applications',
            href: index(),
        },
        {
            title: 'Review',
        },
    ],
};

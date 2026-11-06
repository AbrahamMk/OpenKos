import { Head, Link, usePage } from '@inertiajs/react';
import { EmptyState, StatusBadge } from '@/components/shared';
import { Card, CardContent } from '@/components/ui/card';
import {
    formatBillingOptionLabel,
    formatDate,
    formatDateTime,
    formatPrice,
} from '@/lib/formatters';
import { t } from '@/lib/i18n';
import { index as applicationsIndex, show } from '@/routes/applications';
import type {
    ApplicantApplication,
    ApplicationIndexPageProps,
    ApplicationsIndexProps,
    OperatorApplication,
} from '@/types';

function offerSummary(application: ApplicantApplication): string | null {
    if (
        !application.rental_amount ||
        !application.rental_currency ||
        !application.rental_billing_unit ||
        !application.rental_billing_interval
    ) {
        return null;
    }

    return `${formatPrice(application.rental_amount, application.rental_currency)} · ${formatBillingOptionLabel(
        application.rental_billing_interval,
        application.rental_billing_unit,
    )}`;
}

function listingTitle(application: ApplicantApplication): string {
    if (application.target_type === 'unit_type') {
        return (
            application.unit_type?.name ??
            application.property?.name ??
            t('Application')
        );
    }

    return application.property?.name ?? t('Application');
}

export default function ApplicationsIndex({
    applications,
    operator,
}: ApplicationsIndexProps) {
    const { props } = usePage<ApplicationIndexPageProps>();

    return (
        <>
            <Head title={t('Applications')} />
            <div className="mx-auto w-full max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <header className="space-y-1">
                    <p className="text-sm text-muted-foreground">
                        {operator
                            ? t(
                                  'Review applicant details and rental offers before updating status',
                              )
                            : t('Your listing applications')}
                    </p>
                    <h1 className="text-3xl font-semibold tracking-tight">
                        {t('Applications')}
                    </h1>
                </header>
                {props.flash?.status && (
                    <p className="rounded-md bg-green-50 p-3 text-sm text-green-800">
                        {props.flash.status}
                    </p>
                )}
                {props.errors?.application && (
                    <p className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                        {props.errors.application}
                    </p>
                )}
                {applications.length === 0 ? (
                    <EmptyState
                        message={
                            operator
                                ? 'Applications from published listings will appear here for review.'
                                : 'Applications you submit for published listings will appear here.'
                        }
                    />
                ) : (
                    <div className="grid gap-4">
                        {applications.map((application) => {
                            const operatorApplication = operator
                                ? (application as OperatorApplication)
                                : null;
                            const offer = offerSummary(application);

                            return (
                                <Link
                                    key={application.id}
                                    href={show(application.id)}
                                    className="rounded-xl focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <Card className="transition-colors hover:bg-muted/30">
                                        <CardContent className="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                                            <div className="space-y-2">
                                                <div>
                                                    <p className="font-medium">
                                                        {listingTitle(
                                                            application,
                                                        )}
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {application.property
                                                            ?.name &&
                                                        application.target_type ===
                                                            'unit_type'
                                                            ? application
                                                                  .property
                                                                  .name
                                                            : null}
                                                        {application.property
                                                            ?.name &&
                                                        application.target_type ===
                                                            'unit_type'
                                                            ? ' · '
                                                            : null}
                                                        {application.target_type ===
                                                        'unit_type'
                                                            ? t('Unit type')
                                                            : t(
                                                                  'Whole property',
                                                              )}
                                                    </p>
                                                </div>
                                                {operatorApplication && (
                                                    <p className="text-sm">
                                                        <span className="font-medium">
                                                            {
                                                                operatorApplication
                                                                    .applicant
                                                                    .name
                                                            }
                                                        </span>
                                                        <span className="text-muted-foreground">
                                                            {' '}
                                                            ·{' '}
                                                            {
                                                                operatorApplication
                                                                    .applicant
                                                                    .email
                                                            }
                                                        </span>
                                                    </p>
                                                )}
                                                {offer && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {t('Offer')}: {offer}
                                                    </p>
                                                )}
                                                {application.intended_move_in_date && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {t('Preferred move-in')}
                                                        :{' '}
                                                        {formatDate(
                                                            application.intended_move_in_date,
                                                        )}
                                                    </p>
                                                )}
                                            </div>
                                            <div className="flex flex-col items-start gap-2 sm:items-end">
                                                <StatusBadge
                                                    domain="application"
                                                    value={application.status}
                                                />
                                                {application.submitted_at && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {formatDateTime(
                                                            application.submitted_at,
                                                        )}
                                                    </p>
                                                )}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        </>
    );
}

ApplicationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Applications',
            href: applicationsIndex(),
        },
    ],
};

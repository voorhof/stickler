<?php

namespace App\Filament\Actions;

use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class ArchiveBulkAction extends StatusToggleBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'archiveBulk';
    }

    protected function getStatusColumn(): string
    {
        return 'archived_at';
    }

    protected function getStatusValue(): CarbonInterface
    {
        return now();
    }

    protected function skipsRecordsAlreadyInState(): bool
    {
        return true;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Archive selected'));

        $this->modalHeading(fn (): string => __('Archive selected :label', ['label' => $this->getTitleCasePluralModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::modal.actions.confirm.label'));

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            return $notification
                ->title(__('Archived :count :label', [
                    'count' => $records->whereNotNull('archived_at')->count(),
                    'label' => $records->whereNotNull('archived_at')->count() > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });

        $this->failureNotificationTitle(function (int $successCount, int $totalCount): string {
            if ($successCount) {
                return __(':count of :total :label archived', [
                    'count' => Number::format($successCount),
                    'total' => Number::format($totalCount),
                    'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]);
            }

            return __('Archiving :label failed', [
                'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkAuthorizationFailureNotificationMessage(function (int $failureCount): string {
            return __('You don\'t have permission to archive :count :label.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkProcessingFailureNotificationMessage(function (int $failureCount): string {
            return __(':count :label could not be archived.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->defaultColor('gray');

        $this->icon(Heroicon::ArchiveBoxArrowDown);

        $this->requiresConfirmation();

        $this->modalIcon(Heroicon::ArchiveBoxArrowDown);
    }
}

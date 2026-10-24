<?php

namespace App\Filament\Actions;

use App\Filament\Filters\IsArchivedFilter;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class DearchiveBulkAction extends StatusToggleBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'dearchiveBulk';
    }

    protected function getStatusColumn(): string
    {
        return 'archived_at';
    }

    protected function getStatusValue(): null
    {
        return null;
    }

    protected function skipsRecordsAlreadyInState(): bool
    {
        return true;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Dearchive selected'));

        $this->modalHeading(fn (): string => __('Dearchive selected :label', ['label' => $this->getTitleCasePluralModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::modal.actions.confirm.label'));

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            return $notification
                ->title(__('Dearchived :count :label', [
                    'count' => $records->whereNull('archived_at')->count(),
                    'label' => $records->whereNull('archived_at')->count() > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });

        $this->failureNotificationTitle(function (int $successCount, int $totalCount): string {
            if ($successCount) {
                return __(':count of :total :label dearchived', [
                    'count' => Number::format($successCount),
                    'total' => Number::format($totalCount),
                    'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]);
            }

            return __('Dearchiving :label failed', [
                'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkAuthorizationFailureNotificationMessage(function (int $failureCount): string {
            return __('You don\'t have permission to dearchive :count :label.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkProcessingFailureNotificationMessage(function (int $failureCount): string {
            return __(':count :label could not be dearchived.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->defaultColor('gray');

        $this->icon(Heroicon::ArchiveBox);

        $this->requiresConfirmation();

        $this->modalIcon(Heroicon::ArchiveBox);

        $this->deselectRecordsAfterCompletion();

        $this->hidden(function (HasTable $livewire): bool {
            $isArchivedFilterState = $livewire->getTableFilterState(IsArchivedFilter::class) ?? [];

            if (array_key_exists('isArchived', $isArchivedFilterState)) {
                return $isArchivedFilterState['isArchived'];
            }

            return false;
        });
    }
}

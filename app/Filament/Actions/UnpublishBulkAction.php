<?php

namespace App\Filament\Actions;

use App\Filament\Filters\IsUnpublishedFilter;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class UnpublishBulkAction extends StatusToggleBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'unpublishBulk';
    }

    protected function getStatusColumn(): string
    {
        return 'published_at';
    }

    protected function getStatusValue(): null
    {
        return null;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Unpublish selected'));

        $this->modalHeading(fn (): string => __('Unpublish selected :label', ['label' => $this->getTitleCasePluralModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::modal.actions.confirm.label'));

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            return $notification
                ->title(__('Unpublished :count :label', [
                    'count' => $records->whereNull('published_at')->count(),
                    'label' => $records->whereNull('published_at')->count() > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });

        $this->failureNotificationTitle(function (int $successCount, int $totalCount): string {
            if ($successCount) {
                return __(':count of :total :label unpublished', [
                    'count' => Number::format($successCount),
                    'total' => Number::format($totalCount),
                    'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]);
            }

            return __('Unpublishing :label failed', [
                'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkAuthorizationFailureNotificationMessage(function (int $failureCount): string {
            return __('You don\'t have permission to unpublish :count :label.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkProcessingFailureNotificationMessage(function (int $failureCount): string {
            return __(':count :label could not be unpublished.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->defaultColor('gray');

        $this->icon(Heroicon::OutlinedArrowDownOnSquareStack);

        $this->requiresConfirmation();

        $this->modalIcon(Heroicon::OutlinedArrowDownOnSquareStack);

        $this->deselectRecordsAfterCompletion();

        $this->hidden(function (HasTable $livewire): bool {
            $isPublishedFilterState = $livewire->getTableFilterState(IsUnpublishedFilter::class) ?? [];

            if (array_key_exists('isActive', $isPublishedFilterState)) {
                return $isPublishedFilterState['isActive'];
            }

            return false;
        });
    }
}

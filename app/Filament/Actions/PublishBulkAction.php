<?php

namespace App\Filament\Actions;

use App\Filament\Filters\IsPublishedFilter;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class PublishBulkAction extends StatusToggleBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'publishBulk';
    }

    protected function getStatusColumn(): string
    {
        return 'published_at';
    }

    protected function getStatusValue(): CarbonInterface
    {
        return now();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Publish selected'));

        $this->modalHeading(fn (): string => __('Publish selected :label', ['label' => $this->getTitleCasePluralModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::modal.actions.confirm.label'));

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            return $notification
                ->title(__('Published :count :label', [
                    'count' => $records->whereNotNull('published_at')->count(),
                    'label' => $records->whereNotNull('published_at')->count() > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });

        $this->failureNotificationTitle(function (int $successCount, int $totalCount): string {
            if ($successCount) {
                return __(':count of :total :label published', [
                    'count' => Number::format($successCount),
                    'total' => Number::format($totalCount),
                    'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]);
            }

            return __('Publishing :label failed', [
                'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkAuthorizationFailureNotificationMessage(function (int $failureCount): string {
            return __('You don\'t have permission to publish :count :label.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkProcessingFailureNotificationMessage(function (int $failureCount): string {
            return __(':count :label could not be published.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->defaultColor('primary');

        $this->icon(Heroicon::OutlinedArrowUpOnSquareStack);

        $this->requiresConfirmation();

        $this->modalIcon(Heroicon::OutlinedArrowUpOnSquareStack);

        $this->deselectRecordsAfterCompletion();

        $this->hidden(function (HasTable $livewire): bool {
            $isPublishedFilterState = $livewire->getTableFilterState(IsPublishedFilter::class) ?? [];

            if (array_key_exists('isActive', $isPublishedFilterState)) {
                return $isPublishedFilterState['isActive'];
            }

            return false;
        });
    }
}

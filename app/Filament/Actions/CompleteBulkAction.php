<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class CompleteBulkAction extends StatusToggleBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'completeBulk';
    }

    protected function getStatusColumn(): string
    {
        return 'completed_at';
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

        $this->label(__('Complete selected'));

        $this->modalHeading(fn (): string => __('Complete selected :label', ['label' => $this->getTitleCasePluralModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::modal.actions.confirm.label'));

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            return $notification
                ->title(__('Completed :count :label', [
                    'count' => $records->whereNotNull('completed_at')->count(),
                    'label' => $records->whereNotNull('completed_at')->count() > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });

        $this->failureNotificationTitle(function (int $successCount, int $totalCount): string {
            if ($successCount) {
                return __(':count of :total :label completed', [
                    'count' => Number::format($successCount),
                    'total' => Number::format($totalCount),
                    'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
                ]);
            }

            return __('Completing :label failed', [
                'label' => $totalCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkAuthorizationFailureNotificationMessage(function (int $failureCount): string {
            return __('You don\'t have permission to complete :count :label.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->missingBulkProcessingFailureNotificationMessage(function (int $failureCount): string {
            return __(':count :label could not be completed.', [
                'count' => Number::format($failureCount),
                'label' => $failureCount > 1 ? $this->getTitleCasePluralModelLabel() : $this->getTitleCaseModelLabel(),
            ]);
        });

        $this->defaultColor('primary');

        $this->icon(Heroicon::OutlinedCheckBadge);

        $this->requiresConfirmation();

        $this->modalIcon(Heroicon::OutlinedCheckBadge);

        $this->deselectRecordsAfterCompletion();

        $this->hidden(function (HasTable $livewire): bool {
            $isCompletedFilterState = $livewire->getTableFilterState('completed') ?? [];

            if (array_key_exists('isActive', $isCompletedFilterState)) {
                return $isCompletedFilterState['isActive'];
            }

            return false;
        });
    }
}

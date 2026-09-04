<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Number;
use Throwable;

class CompleteBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'completeBulk';
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

        $this->authorizeIndividualRecords('update');

        $this->authorize(function (): bool {
            $livewire = $this->getLivewire();

            if (! method_exists($livewire, 'getTable')) {
                return false;
            }

            $model = $livewire->getTable()->getModel();

            return is_string($model) && auth()->user()?->can('update', $model) === true;
        });

        $this->modalIcon(Heroicon::OutlinedCheckBadge);

        $this->action(function (): void {
            $this->process(static function (CompleteBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                if (! $action->shouldFetchSelectedRecords()) {
                    try {
                        $action->reportBulkProcessingSuccessfulRecordsCount(
                            $action->getSelectedRecordsQuery()->whereNull('completed_at')->update(['completed_at' => now()]),
                        );
                    } catch (Throwable $exception) {
                        $action->reportCompleteBulkProcessingFailure();

                        report($exception);
                    }

                    return;
                }

                $isFirstException = true;

                $records->each(static function (Model $record) use ($action, &$isFirstException): void {
                    if ($record->completed_at !== null) {
                        return;
                    }

                    try {
                        $record->update(['completed_at' => now()]) || $action->reportBulkProcessingFailure();
                    } catch (Throwable $exception) {
                        $action->reportBulkProcessingFailure();

                        if ($isFirstException) {
                            // Only report the first exception to not flood error logs. Even if Filament
                            // did not catch exceptions like this, only the first would be reported
                            // as the rest of the process would be halted.
                            report($exception);

                            $isFirstException = false;
                        }
                    }
                });
            });
        });

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

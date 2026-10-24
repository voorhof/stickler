<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Throwable;

abstract class StatusToggleBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    abstract protected function getStatusColumn(): string;

    abstract protected function getStatusValue(): mixed;

    protected function skipsRecordsAlreadyInState(): bool
    {
        return false;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizeIndividualRecords('update');

        $this->authorize(function (): bool {
            $livewire = $this->getLivewire();

            if (! method_exists($livewire, 'getTable')) {
                return false;
            }

            $model = $livewire->getTable()->getModel();

            return is_string($model) && auth()->user()?->can('update', $model) === true;
        });

        $this->action(function (): void {
            $this->process(static function (StatusToggleBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                $column = $action->getStatusColumn();
                $value = $action->getStatusValue();
                $skipsRecordsInState = $action->skipsRecordsAlreadyInState();

                if (! $action->shouldFetchSelectedRecords()) {
                    try {
                        $query = $action->getSelectedRecordsQuery();

                        if ($skipsRecordsInState && $value === null) {
                            $query->whereNotNull($column);
                        } elseif ($skipsRecordsInState) {
                            $query->whereNull($column);
                        }

                        $action->reportBulkProcessingSuccessfulRecordsCount(
                            $query->update([$column => $value]),
                        );
                    } catch (Throwable $exception) {
                        $action->reportCompleteBulkProcessingFailure();

                        report($exception);
                    }

                    return;
                }

                $isFirstException = true;

                $records->each(static function (Model $record) use ($action, $column, $value, $skipsRecordsInState, &$isFirstException): void {
                    $isAlreadyInState = ($record->{$column} !== null) === ($value !== null);

                    if ($skipsRecordsInState && $isAlreadyInState) {
                        return;
                    }

                    try {
                        $record->update([$column => $value]) || $action->reportBulkProcessingFailure();
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
    }
}

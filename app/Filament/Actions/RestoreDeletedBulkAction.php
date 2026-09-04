<?php

namespace App\Filament\Actions;

use Filament\Actions\RestoreBulkAction as BaseRestoreBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class RestoreDeletedBulkAction extends BaseRestoreBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'restoreDeletedBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizeIndividualRecords('restore');

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            $notification
                ->title(__(':count :model items restored', ['count' => $records->count(), 'model' => __($this->getTitleCaseModelLabel())]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);

            return $notification;
        });
    }
}

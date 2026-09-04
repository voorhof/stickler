<?php

namespace App\Filament\Actions;

use Filament\Actions\DeleteBulkAction as BaseDeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class SoftDeleteBulkAction extends BaseDeleteBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'softDeleteBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizeIndividualRecords('delete');

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            $notification
                ->title(__(':count :model items deleted', ['count' => $records->count(), 'model' => __($this->getTitleCaseModelLabel())]))
                ->warning()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);

            return $notification;
        });
    }
}

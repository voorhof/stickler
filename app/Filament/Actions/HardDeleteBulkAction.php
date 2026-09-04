<?php

namespace App\Filament\Actions;

use Filament\Actions\ForceDeleteBulkAction as BaseForceDeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class HardDeleteBulkAction extends BaseForceDeleteBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'hardDeleteBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizeIndividualRecords('forceDelete');

        $this->icon(Heroicon::XMark);

        $this->successNotification(function (Notification $notification, Collection $records): Notification {
            $notification
                ->title(__(':count :model items permanently deleted', ['count' => $records->count(), 'model' => __($this->getTitleCaseModelLabel())]))
                ->warning()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);

            return $notification;
        });
    }
}

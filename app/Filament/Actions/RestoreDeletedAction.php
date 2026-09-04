<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use Filament\Actions\RestoreAction as BaseRestoreAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class RestoreDeletedAction extends BaseRestoreAction
{
    public static function getDefaultName(): ?string
    {
        return 'restoreDeleted';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedArrowUturnLeft);

        $this->color('info');

        $this->outlined();

        $this->size('sm');

        $this->successNotification(function (Notification $notification, Model $record): Notification {
            $notification
                ->title(__('Yay! :model :title (ID: :id) is back from the bin', ['model' => __($this->getTitleCaseModelLabel()), 'title' => $record->title, 'id' => $record->id]))
                ->success()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);

            return $notification;
        });
    }
}

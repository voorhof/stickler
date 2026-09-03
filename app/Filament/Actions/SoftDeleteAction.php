<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use Filament\Actions\DeleteAction as BaseDeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class SoftDeleteAction extends BaseDeleteAction
{
    public static function getDefaultName(): ?string
    {
        return 'softDelete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedTrash);

        $this->outlined();

        $this->size('sm');

        $this->successNotification(function (Notification $notification, Model $record): Notification {
            $notification
                ->title(__('Hey! :model :title (ID: :id) has been removed', ['model' => __($this->getTitleCaseModelLabel()), 'title' => $record->title, 'id' => $record->id]))
                ->warning()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);

            return $notification;
        });
    }
}

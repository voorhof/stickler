<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ArchiveAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'archive';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Archive :model', ['model' => __($this->getTitleCaseModelLabel())]));

        $this->icon(Heroicon::OutlinedArchiveBoxArrowDown);

        $this->color(fn (): string => 'gray');

        $this->outlined();

        $this->size('sm');

        $this->hidden(fn (): bool => $this->getRecord()?->archived_at !== null);

        $this->authorize(fn (): string => 'update');

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->action(function (): void {
            // Get the record
            $record = $this->getRecord();

            // Archive the record
            $record->update(['archived_at' => now()]);

            // Refresh the record in the model instance
            $record->refresh();

            // Refresh the form data to update the archive toggle and archived_at field
            $livewire = $this->getLivewire();
            $livewire->refreshFormData(['archive', 'archived_at']);

            // Reset the saved data hash so the unsaved changes alert is not triggered
            $livewire->savedDataHash = md5((string) str(json_encode($livewire->data, JSON_UNESCAPED_UNICODE))->replace('\\', ''));

            // Send a custom notification
            Notification::make()
                ->title(__('Ok! :model :title (ID: :id) has been archived.', ['model' => $this->getTitleCaseModelLabel(), 'title' => $record->title, 'id' => $record->id]))
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}

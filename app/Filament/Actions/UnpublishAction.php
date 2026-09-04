<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class UnpublishAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'unpublish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Unpublish :model', ['model' => $this->getTitleCaseModelLabel()]));

        $this->icon(Heroicon::OutlinedArrowDownOnSquare);

        $this->color(fn (): string => 'gray');

        $this->outlined();

        $this->size('sm');

        $this->hidden(fn (): bool => $this->getRecord()?->published_at === null);

        $this->authorize(fn (): string => 'update');

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->action(function (): void {
            // Get the record
            $record = $this->getRecord();

            // Unpublish the record
            $record->update(['published_at' => null]);

            // Refresh the record in the model instance
            $record->refresh();

            // Refresh the form data to update the published_at field
            $livewire = $this->getLivewire();
            $livewire->refreshFormData(['published_at']);

            // Reset the saved data hash so the unsaved changes alert is not triggered
            $livewire->savedDataHash = md5((string) str(json_encode($livewire->data, JSON_UNESCAPED_UNICODE))->replace('\\', ''));

            // Send a custom notification
            Notification::make()
                ->title(__('Ok! :model :title (ID: :id) has been unpublished.', ['model' => $this->getTitleCaseModelLabel(), 'title' => $record->title, 'id' => $record->id]))
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}

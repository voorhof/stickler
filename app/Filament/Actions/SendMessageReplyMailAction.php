<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Actions;

use App\Mail\ContactMessageReply;
use App\Models\Message;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class SendMessageReplyMailAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendMessageReply';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Send email'));

        $this->icon(Heroicon::OutlinedEnvelope);

        $this->color(fn (): string => 'info');

        $this->outlined();

        $this->size('sm');

        $this->hidden(fn (): bool => $this->getRecord()?->reply === null);

        $this->authorize(auth()->user()?->can('update messages') ?? false);

        $this->rateLimit(fn (): int => 1);

        $this->requiresConfirmation();

        $this->modalHeading(fn (): string => __('Send email'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('Send email'));

        $this->action(function (): void {
            // Get the message to send the reply mail
            $message = Message::find($this->getRecord()->id);

            // Send mail with temporary password to the user
            Mail::queue(new ContactMessageReply($message));

            // Log an activity that the message has been replied to
            activity()
                ->performedOn($message)
                ->event('updated')
                ->withProperties(['message' => $message])
                ->log(__('This item has been :event', ['event' => strtolower(__('Replied'))]));

            // Create notification
            Notification::make()
                ->title(__('Send email'))
                ->info()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}

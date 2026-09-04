<?php

namespace App\Providers\Spatie;

use App\Models\User;
use App\Services\Spatie\BackupService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Events\BackupWasSuccessful;

class BackupServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Send notification when backup is successful
        Event::listen(BackupWasSuccessful::class, function (BackupWasSuccessful $event): void {
            BackupService::clearCache($event->diskName);

            foreach (User::all() as $user) {
                if ($user->can('read backups')) {
                    Notification::make()
                        ->title(__('backups.backup_successful_subject_title'))
                        ->body(__('backups.backup_successful_body', [
                            'application_name' => config('app.name'),
                            'disk_name' => $event->diskName,
                        ]))
                        ->success()
                        ->sendToDatabase($user, isEventDispatched: true);
                }
            }
        });
    }
}

<?php

/** @noinspection PhpUndefinedMethodInspection */

namespace App\Filament\Pages\Auth;

use App\Filament\Resources\Users\Schemas\Components\AvatarUpload;
use App\Filament\Resources\Users\Schemas\Components\NameInput;
use Closure;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    // Using the HasUnsavedDataChangesAlert trait enables the unsaved changes alert.
    // This behaviour is set as default in the AdminPanelProvider,
    // but does not apply as standard to this page.
    // * $this->rememberData() is also added to the afterSave() method
    use HasUnsavedDataChangesAlert;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make(__('Information'))
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->schema([
                                AvatarUpload::make(),
                                NameInput::make(),
                                $this->getEmailFormComponent()
                                    ->autocomplete('username'),
                                $this->getPasswordFormComponent()
                                    ->rules([
                                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                            $currentPassword = $get('currentPassword');

                                            if (filled($value) && filled($currentPassword) && Hash::check($value, auth()->user()->password)) {
                                                $fail(__('Your new password must be different from your current password.'));
                                            }
                                        },
                                    ]),
                                $this->getPasswordConfirmationFormComponent(),
                                $this->getCurrentPasswordFormComponent(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Ok! Profile has been saved.');
    }

    protected function afterSave(): void
    {
        // Remember the new state after saving so unsaved changes alert clears / resets
        $this->rememberData();

        // Persist the standard Filament save notification to the database for the current user
        $notification = $this->getSavedNotification();

        if ($notification !== null && auth()->check()) {
            $notification->sendToDatabase(auth()->user(), isEventDispatched: true);
        }
    }
}

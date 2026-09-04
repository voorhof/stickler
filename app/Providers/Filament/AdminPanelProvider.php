<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Http\Middleware\SetLocaleFromUserPreference;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            ->strictAuthorization() // Throws an exception if a resource model policy or policy method does not exist.
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->profile(EditProfile::class, isSimple: false)
            ->renderHook(
                PanelsRenderHook::SCRIPTS_BEFORE,
                fn (): string => view('filament.file-upload-locale-alias')->render(),
            )
            ->userMenuItems([
                'locale-en_US' => Action::make('locale-en_US')
                    ->label(fn (): string => app()->getLocale() === 'en_US' ? '✓ English' : 'English')
                    ->url(fn (): string => route('filament.admin.locale.update', ['locale' => 'en_US'])),
                'locale-nl_BE' => Action::make('locale-nl_BE')
                    ->label(fn (): string => app()->getLocale() === 'nl_BE' ? '✓ Nederlands' : 'Nederlands')
                    ->url(fn (): string => route('filament.admin.locale.update', ['locale' => 'nl_BE'])),
            ])
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->favicon(asset('art/favicons/favicon-32x32.png'))
            ->maxContentWidth(Width::ScreenExtraLarge) // Default is 'SevenExtraLarge' and equals to 80rem (1280px)
            ->sidebarWidth('17rem') // Default is '20rem'
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->navigationItems([
                // Append navigation items to the default Filament sidebar navigation
                NavigationItem::make(fn () => 'Log Viewer ↗')
                    ->url(fn (): string => route('log-viewer.index'), shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedSquare3Stack3d)
                    ->group(fn () => __('Settings')) // optional: place it in a group
                    ->sort(9800), // optional: push it to the bottom
                NavigationItem::make(fn () => 'Telescope ↗')
                    ->url(fn (): string => url(config('telescope.path')), shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedSparkles)
                    ->group(fn () => __('Settings'))
                    ->sort(9900),
            ])
            ->globalSearchResourceOptIn()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                SetLocaleFromUserPreference::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ], isPersistent: true)
            ->plugins([
                ResizedColumnPlugin::make()
                    ->preserveOnDB(),
            ]);
    }
}

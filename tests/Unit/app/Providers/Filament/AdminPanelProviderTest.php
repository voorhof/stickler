<?php

use App\Filament\Pages\Auth\EditProfile;
use App\Http\Middleware\SetLocaleFromUserPreference;
use App\Providers\Filament\AdminPanelProvider;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

test('it extends PanelProvider', function () {
    $provider = new AdminPanelProvider(app());

    expect($provider)->toBeInstanceOf(PanelProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AdminPanelProvider::class);
});

test('it configures the admin panel with correct id and path', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->getId())->toBe('admin')
        ->and($panel->getPath())->toBe('admin')
        ->and($panel->isDefault())->toBeTrue();
});

test('it configures panel features and authentication options', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->hasUnsavedChangesAlerts())->toBeTrue()
        ->and($panel->hasDatabaseTransactions())->toBeTrue()
        ->and($panel->hasDatabaseNotifications())->toBeTrue()
        ->and($panel->hasLogin())->toBeTrue()
        ->and($panel->hasPasswordReset())->toBeTrue()
        ->and($panel->hasEmailVerification())->toBeTrue()
        ->and($panel->hasEmailChangeVerification())->toBeTrue()
        ->and($panel->hasProfile())->toBeTrue()
        ->and($panel->getProfilePage())->toBe(EditProfile::class);
});

test('it configures layout dimensions and colors', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->getMaxContentWidth())->toBe(Width::ScreenExtraLarge)
        ->and($panel->getSidebarWidth())->toBe('17rem')
        ->and($panel->getColors()['primary'])->toBe(Color::Amber);
});

test('it configures panel middleware correctly', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    $middleware = $panel->getMiddleware();

    expect($middleware)->toContain(
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
    );
});

test('it configures auth middleware correctly', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->getAuthMiddleware())->toContain(Authenticate::class);
});

test('it configures resized column plugin', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->hasPlugin('asmit-resized-column'))->toBeTrue()
        ->and($panel->getPlugin('asmit-resized-column'))->toBeInstanceOf(ResizedColumnPlugin::class);
});

test('it registers render hooks on boot', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);
    $panel->boot();

    expect(FilamentView::hasRenderHook(PanelsRenderHook::SCRIPTS_BEFORE))->toBeTrue();
});

test('it configures resource, page, and widget discovery paths', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->getResourceDirectories())->toContain(app_path('Filament/Resources'))
        ->and($panel->getPageDirectories())->toContain(app_path('Filament/Pages'))
        ->and($panel->getWidgetDirectories())->toContain(app_path('Filament/Widgets'));
});

test('it configures brand logo', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    expect($panel->getBrandLogo())->not->toBeNull();
});

test('it registers the admin panel in Filament manager', function () {
    $panel = Filament::getPanel('admin');

    expect($panel)->not->toBeNull()
        ->and($panel->getId())->toBe('admin')
        ->and($panel->isDefault())->toBeTrue();
});

test('it configures navigation items correctly', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    $navigationItems = $panel->getNavigationItems();

    expect($navigationItems)->toHaveCount(2);

    $logViewerItem = $navigationItems[0];
    expect($logViewerItem->getLabel())->toBe('Log Viewer ↗')
        ->and($logViewerItem->getUrl())->toBe(route('log-viewer.index'))
        ->and($logViewerItem->getIcon())->toBe(Heroicon::OutlinedSquare3Stack3d)
        ->and($logViewerItem->getGroup())->toBe(__('Settings'))
        ->and($logViewerItem->getSort())->toBe(9800);

    $telescopeItem = $navigationItems[1];
    expect($telescopeItem->getLabel())->toBe('Telescope ↗')
        ->and($telescopeItem->getUrl())->toBe(url(config('telescope.path')))
        ->and($telescopeItem->getIcon())->toBe(Heroicon::OutlinedSparkles)
        ->and($telescopeItem->getGroup())->toBe(__('Settings'))
        ->and($telescopeItem->getSort())->toBe(9900);
});

test('it configures user menu items correctly', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(new Panel);

    $userMenuItems = $panel->getUserMenuItems();

    expect($userMenuItems)->toHaveKey('locale-en_US')
        ->and($userMenuItems)->toHaveKey('locale-nl_BE');

    $enItem = $userMenuItems['locale-en_US'];
    $nlItem = $userMenuItems['locale-nl_BE'];

    expect($enItem->getUrl())->toBe(route('filament.admin.locale.update', ['locale' => 'en_US']))
        ->and($nlItem->getUrl())->toBe(route('filament.admin.locale.update', ['locale' => 'nl_BE']));

    $originalLocale = app()->getLocale();

    app()->setLocale('en_US');
    expect($enItem->getLabel())->toBe('✓ English')
        ->and($nlItem->getLabel())->toBe('Nederlands');

    app()->setLocale('nl_BE');
    expect($enItem->getLabel())->toBe('English')
        ->and($nlItem->getLabel())->toBe('✓ Nederlands');

    app()->setLocale($originalLocale);
});

<?php

use Spatie\Sluggable\Actions\BuildSelfHealingRouteKeyAction;
use Spatie\Sluggable\Actions\ExtractIdentifierFromSelfHealingRouteKeyAction;
use Spatie\Sluggable\Actions\GenerateSlugAction;

return [
    /*
     * The actions that perform low-level operations of this package.
     *
     * You can extend the default actions and specify your own actions
     * here to customize the package's behavior.
     */
    'actions' => [
        'generate_slug' => GenerateSlugAction::class,
        'build_self_healing_route_key' => BuildSelfHealingRouteKeyAction::class,
        'extract_identifier_from_self_healing_route_key' => ExtractIdentifierFromSelfHealingRouteKeyAction::class,
    ],

    /**
     * The model's excluded values for slug generation.
     */
    'excluded_from_slug' => [
        'admin',
        'algemene-voorwaarden',
        'brandbook',
        'cart',
        'checkout',
        'contact',
        'cookie-policy',
        'dashboard',
        'email-verificatie',
        'filament',
        'home',
        'login',
        'logout',
        'order-confirmed',
        'pages',
        'page',
        'posts',
        'post',
        'privacy-policy',
        'products',
        'product',
        'projects',
        'project',
        'registreer',
        'storage',
        'test',
        'up',
        'wachtwoord-bevestigen',
        'wachtwoord-reset',
        'wachtwoord-update',
        'wachtwoord-vergeten',
        'welcome',
        'xxx',
        'yyy',
        'zzz',
    ],
];

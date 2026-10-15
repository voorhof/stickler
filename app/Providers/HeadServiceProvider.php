<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

class HeadServiceProvider extends ServiceProvider
{
    /**
     * Prodider for the Laravel Head package.
     * https://laravel.com/framework/docs/13.x/head
     *
     * Bootstrap services.
     */
    public function boot(): void
    {
        Head::defaults(function (HeadBuilder $head) {
            $head
                ->title(config('app.name'), prefix: config('app.name').' | ')
                ->description(config('app.name').' website template')
                ->canonical() // Page url
                ->robots([
                    RobotsRule::Index,
                    RobotsRule::Follow,
                    'max-image-preview:large, max-snippet:-1, max-video-preview:-1',
                ])
                ->og(
                    type: OgType::Website,
                    url: config('app.url'),
                    image: config('app.url').'/art/logo/logo_bg.png',
                    siteName: str_replace('https://', '', config('app.url')),
                    locale: config('app.locale'),
                )
                ->viewport('width=device-width, initial-scale=1')
                ->meta('keywords', mb_strtolower(config('app.name')).', laravel, starter kit')
                ->referrer('strict-origin-when-cross-origin')
                ->applicationName(config('app.name'))
                ->colorScheme('light dark')
                ->appleWebAppTitle(config('app.name'))
                ->webAppCapable()
                ->pwa(
                    name: config('app.name'),
                    manifest: asset('art/favicons/site.webmanifest'),
                    themeColor: '#ffffff',
                    appleTouchIcon: asset('art/favicons/apple-touch-icon.png'),
                    appleWebAppStatusBarStyle: 'black',
                )
                ->meta('msapplication-TileColor', '#ffffff')
                ->favicon(asset('art/favicons/favicon.ico'), ImageType::Ico)
                ->icon(asset('art/favicons/favicon-16x16.png'), ImageType::Png, '16x16')
                ->icon(asset('art/favicons/favicon-32x32.png'), ImageType::Png, '32x32')
                ->icon(asset('art/favicons/favicon-48x48.png'), ImageType::Png, '48x48')
                ->icon(asset('art/favicons/favicon-96x96.png'), ImageType::Png, '96x96')
                ->icon(asset('art/favicons/favicon.svg'), ImageType::Svg)
                ->maskIcon(asset('art/favicons/favicon.svg'), '#000000')
                ->manifest(asset('art/favicons/site.webmanifest'))
                ->link('sitemap', Storage::url('sitemap.xml'), ['type' => 'application/xml', 'title' => 'Sitemap']);
            // ->preconnect('https://fonts.example.com')
        });

        Head::errors(function (ErrorPages $errors) {
            $errors->defaults(robots: 'noindex, follow');

            $errors->status(
                404,
                title: __('Not Found'),
                description: __('Nothing to show.'),
            );

            $errors->status(
                500,
                title: __('Forbidden'),
                description: __('Forbidden'),
            );
        });
    }
}

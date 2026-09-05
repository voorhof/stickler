<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    @stack('scripts-head-top')
    {{-- Required meta --}}
    <meta charset="utf-8">
    <script>
        {{-- Set the initial theme before main app.js loads to prevent a flash of the wrong theme on page load/navigation. --}}
        (() => {
            let storedTheme = null

            try {
                storedTheme = localStorage.getItem('color-scheme-theme')
            } catch {
                console.error('Failed to retrieve stored theme from localStorage.')
            }

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
            const theme = !storedTheme || storedTheme === 'auto' || storedTheme === 'system'
                ? (prefersDark ? 'dark' : 'light')
                : storedTheme

            document.documentElement.setAttribute('data-theme', theme)
        })()
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}  |  {{ $headTitle ?? __('Welcome') }}</title>
    <meta name="description" content="{{ $metaDescription ?? config('app.name').' website template' }}">
    <meta name="keywords" content="{{ strtolower(config('app.name')) }}, laravel, starter kit">
    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'>
    {{-- Favicons --}}
    <link rel="icon" type="image/png" href="{{ asset('art/favicons/favicon-16x16.png') }}" sizes="16x16">
    <link rel="icon" type="image/png" href="{{ asset('art/favicons/favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('art/favicons/favicon-48x48.png') }}" sizes="48x48">
    <link rel="icon" type="image/png" href="{{ asset('art/favicons/favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('art/favicons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('art/favicons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('art/favicons/apple-touch-icon.png') }}">
    <meta name="application-name" content="Stickler">
    <meta name="apple-mobile-web-app-title" content="Stickler">
    <link rel="manifest" href="{{ asset('art/favicons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('art/favicons/favicon.svg') }}" color="#000000">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    {{-- Sharing --}}
    <meta property="og:locale" content="{{ config('app.locale') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="{{ config('app.name') }} website template">
    <meta property="og:url" content="{{ config('app.url') }}/">
    <meta property="og:site_name" content="{{ str_replace('https://', '', config('app.url')) }}">
    <meta property="og:image" content="{{ config('app.url') }}/art/logo/pietjeprecies_logo_bg.png">
    <meta property="og:image:secure_url" content="{{ config('app.url') }}/art/logo/pietjeprecies_logo_bg.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="2160">
    <meta property="og:image:height" content="960">
    <meta property="og:image:alt" content="{{ config('app.name') }} logo">
    {{-- Sitemap --}}
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ Storage::url('sitemap.xml') }}">
    {{-- Styles / Scripts --}}
    @fonts
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @endif
    @stack('scripts-head-bottom')
</head>
<body>
@stack('scripts-body-top')

<div id="app">
    <x-navbar />

    <x-offcanvas />

    <x-header />

    <main>
        {{ $slot }}
    </main>

    <x-footer />
</div>

@include('cookie-consent::index')
@stack('modals-bottom')
@stack('scripts-body-bottom')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $user?->locale ?? 'nl_BE') }}" data-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('scripts-head-top')
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
    {{-- Laravel HeadServiceProvider  --}}
    @head
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

<x-footer-pietje />

@include('cookie-consent::index')
@stack('modals-bottom')
@stack('scripts-body-bottom')
</body>
</html>

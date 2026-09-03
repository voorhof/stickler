@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Icons\Heroicon;
@endphp
<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="fi-account-widget-heading">
            {{ config('app.name') }}
        </h2>

        <div style="display: flex; gap: 1rem; justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem; padding-top: 0.25rem">
                <x-filament::link
                    color="primary"
                    href="{{ route('welcome.index') }}"
                    :icon="Heroicon::OutlinedHome"
                    :icon-position="IconPosition::Before"
                >@lang('Homepage')</x-filament::link>

                @can('edit settings')
                    <x-filament::link
                        color="secondary"
                        href="{{ route('filament.admin.sitemap.generate') }}"
                        :icon="Heroicon::OutlinedArrowPath"
                        :icon-position="IconPosition::Before"
                    >@lang('Generate sitemap')</x-filament::link>
                @endcan
            </div>

            <div>
                <x-filament::link
                    color="secondary"
                    href="https://github.com/voorhof/pietjeprecies"
                    :icon="Heroicon::ArrowTopRightOnSquare"
                    :icon-position="IconPosition::After"
                    target="_blank"
                >@lang('GitHub')</x-filament::link>

            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

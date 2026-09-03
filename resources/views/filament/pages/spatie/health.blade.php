@php use Illuminate\Support\Carbon; @endphp
<x-filament-panels::page>
    @if($checkResults && count($checkResults->storedCheckResults ?? []))
        @php($lastRanAt = Carbon::parse($checkResults->finishedAt))

        <x-filament::section>
            <x-slot name="heading">
                {{ __('Status check results from') }}
                {{ $lastRanAt->diffForHumans() }}
            </x-slot>

            <x-slot name="description">
                @if($lastRanAt->diffInMinutes() > 61)
                    <span class="fi-in-text-item fi-color fi-color-danger fi-text-color-600 dark:fi-text-color-400">
                        {{ __('Results are stale (older than 60 minutes).') }}
                    </span>
                @else
                    {{ __('Results are up to date.') }}
                @endif
            </x-slot>

            <div class="fi-sc fi-sc-has-gap fi-grid lg:fi-grid-cols"
                 style="--cols-lg: repeat(3, minmax(0, 1fr)); --cols-default: repeat(1, minmax(0, 1fr));">
                @foreach($checkResults->storedCheckResults as $result)
                    <x-filament::section class="fi-filament-info-widget">
                            <x-filament.health-status-indicator :result="$result"/>

                            <div>
                                <h3 class="fi-section-header-heading" style="--text-base: 1.125rem;">
                                    {{ $result->label }}
                                </h3>

                                <p class="fi-section-header-description">
                                    {{ ! empty($result->notificationMessage)
                                        ? $result->notificationMessage
                                        : $result->shortSummary }}
                                </p>
                            </div>
                    </x-filament::section>
                @endforeach
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <p>
                {{ __('No health check results yet.') }}
            </p>
        </x-filament::section>
    @endif
</x-filament-panels::page>

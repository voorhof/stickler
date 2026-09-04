<div class="fi-ta-icon fi-inline {{$backgroundColor($result->status)}}"
     style="width: unset; align-self: baseline; padding: calc(var(--spacing) * 1.5); margin-top: calc(var(--spacing) * -1.5); background-color: var(--color-50); border-radius: 50%;">
    <svg class="fi-icon fi-size-lg fi-color {{$iconColor($result->status)}} fi-text-color-600 dark:fi-text-color-600"
         style="--spacing: 0.3125rem;"
         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">

        @if($icon($result->status) == 'check-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        @elseif($icon($result->status) == 'exclamation-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        @elseif($icon($result->status) == 'arrow-right-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        @elseif($icon($result->status) == 'x-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        @else
            {{-- question-mark-circle --}}
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
        @endif
    </svg>
</div>

@if(method_exists($this, 'resetColumnWidths'))
    <x-filament::icon-button
        wire:click="resetColumnWidths"
        icon="heroicon-o-arrow-path"
        color="gray"
        :tooltip="__('Reset table')"
    />
@endif

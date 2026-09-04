<x-filament-panels::page>
    {{ $this->content }}

    <x-filament::modal id="backup-option" width="lg">
        <x-slot name="heading">
            {{ __('backups.backup_option_select') }}
        </x-slot>
        <x-slot name="footer">
            <div class="fi-sc fi-sc-has-gap fi-grid lg:fi-grid-cols"
                 style="--cols-lg: repeat(3, minmax(0, 1fr)); --cols-default: repeat(1, minmax(0, 1fr)); --spacing: .1rem;">
                <x-filament::button wire:click="create('only-db')" color="primary" outlined>
                    {{ __('backups.backup_option_db') }}
                </x-filament::button>
                <x-filament::button wire:click="create('only-files')" color="primary" outlined>
                    {{ __('backups.backup_option_files') }}
                </x-filament::button>
                <x-filament::button wire:click="create('all')" color="primary">
                    {{ __('backups.backup_option_all') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

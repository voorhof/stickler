<?php

namespace App\Filament\Pages\Spatie;

use App\Services\Spatie\BackupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination as SpatieBackupDestination;
use Spatie\Backup\Commands\BackupCommand;
use UnitEnum;

class Backups extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CircleStack;

    protected string $view = 'filament.pages.spatie.backups';

    protected static ?int $navigationSort = 9700;

    public function getTitle(): string|Htmlable
    {
        return __('backups.backup_page_title');
    }

    public static function getNavigationLabel(): string
    {
        return __('backups.backup_page_title');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Settings');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view backups') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->button()
                ->label(__('backups.create_backup'))
                ->action('openOptionModal')
                ->visible(auth()->user()->can('create backups')),
        ];
    }

    public function openOptionModal(): void
    {
        $this->dispatch('open-modal', id: 'backup-option');
    }

    public function create(string $option = 'all'): void
    {
        Artisan::queue(BackupCommand::class, [
            '--only-db' => $option === 'only-db',
            '--only-files' => $option === 'only-files',
            '--filename' => match ($option) {
                'all' => null,
                default => str_replace('_', '-', $option)
                    .'-'.date('Y-m-d-H-i-s').'.zip'
            },
        ]);

        foreach (BackupService::getDisks() as $disk) {
            BackupService::clearCache($disk);
        }

        $this->dispatch('close-modal', id: 'backup-option');

        Notification::make()
            ->title(__('backups.backup_queued'))
            ->success()
            ->send();

        $this->resetTable();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('backups.backup_status_title'))
                    ->schema(fn (): array => $this->getBackupStatusComponents()),
                EmbeddedTable::make(),
            ]);
    }

    /**
     * @return array<Grid>
     */
    protected function getBackupStatusComponents(): array
    {
        return collect(BackupService::getBackupDestinationStatusData())
            ->map(fn (array $status): Grid => Grid::make(7)
                ->schema([
                    TextEntry::make('name')->label(__('backups.backup_name'))->state($status['name']),
                    TextEntry::make('disk')->label(__('backups.disk'))->state($status['disk']),
                    IconEntry::make('reachable')->label(__('backups.reachable'))->boolean()->state($status['reachable']),
                    IconEntry::make('healthy')->label(__('backups.healthy'))->boolean()->state($status['healthy']),
                    TextEntry::make('amount')->label(__('backups.amount'))->state($status['amount']),
                    TextEntry::make('newest')->label(__('backups.newest'))->state($status['newest']),
                    TextEntry::make('usedStorage')->label(__('backups.storage'))->state($status['usedStorage']),
                ]))
            ->all();
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->records(function () {
                $data = [];
                foreach (BackupService::getDisks() as $disk) {
                    $data = array_merge($data, BackupService::getBackupDestinationData($disk));
                }

                return collect($data);
            })
            ->columns([
                TextColumn::make('path')->label(__('backups.path'))->searchable()->sortable(),
                TextColumn::make('disk')->label(__('backups.disk'))->searchable()->sortable(),
                TextColumn::make('date')->label(__('backups.date'))->dateTime()->sortable(),
                TextColumn::make('size')->label(__('backups.size'))->badge(),
            ])
            ->filters([
                SelectFilter::make('disk')
                    ->label(__('backups.disk'))
                    ->options(BackupService::getFilterDisks()),
            ])
            ->recordActions([
                Action::make('download')
                    ->label(__('backups.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(auth()->user()->can('download backups'))
                    ->action(fn (array $record) => Storage::disk($record['disk'])->download($record['path'])),

                Action::make('delete')
                    ->label(__('backups.delete'))
                    ->icon('heroicon-o-trash')
                    ->visible(auth()->user()->can('delete backups'))
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (array $record) {
                        SpatieBackupDestination::create($record['disk'], config('backup.backup.name'))
                            ->backups()
                            ->first(fn (Backup $backup): bool => $backup->path() === $record['path'])
                            ->delete();

                        BackupService::clearCache($record['disk']);

                        Notification::make()
                            ->title(__('backups.backup_delete_success'))
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),
            ]);
    }
}

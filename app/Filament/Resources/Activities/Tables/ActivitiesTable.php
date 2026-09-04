<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use App\Filament\Traits\FormatsModelType;
use App\Models\Activity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    use FormatsModelType;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IdColumn::make(),
                TextColumn::make('log_name')
                    ->label(__('Log name'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event')
                    ->label(__('Action'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(32),
                TextColumn::make('subject_type')
                    ->label(__('Subject'))
                    ->formatStateUsing(fn (?string $state): string => self::formatModelType(type: $state)),
                TextColumn::make('subject_id')
                    ->label(__('Subject ID'))
                    ->numeric(),
                TextColumn::make('causer_type')
                    ->label(__('Causer'))
                    ->formatStateUsing(fn (?string $state): string => self::formatModelType(type: $state)),
                TextColumn::make('causer_id')
                    ->label(__('Causer ID'))
                    ->numeric(),
                CreatedColumn::make(
                    isToggledHiddenByDefault: false,
                ),
                UpdatedColumn::make(),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label(__('Subject'))
                    ->options(fn () => Activity::query()
                        ->whereNotNull('subject_type')
                        ->distinct()
                        ->pluck('subject_type', 'subject_type')
                        ->map(fn (string $value): string => self::formatModelType(type: $value))
                        ->toArray(),
                    )
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'] ?? null, fn (Builder $query, $value) => $query->where('subject_type', $value))),
                SelectFilter::make('event')
                    ->label(__('Action'))
                    ->options(fn () => Activity::query()
                        ->whereNotNull('event')
                        ->distinct()
                        ->pluck('event', 'event')
                        ->toArray(),
                    )
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'] ?? null, fn (Builder $query, $value) => $query->where('event', $value))),
                SelectFilter::make('log_name')
                    ->label(__('Log name'))
                    ->options(fn () => Activity::distinct()->pluck('log_name', 'log_name')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query->where('log_name', '!=', 'activity');
                        }

                        return $query->where('log_name', $data['value']);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['value'] ?? null)) {
                            // If there are no records in the 'activity' log, return null to hide the default filter.
                            if (! Activity::where('log_name', 'activity')->exists()) {
                                return null;
                            }

                            return __("Hiding 'activity' logs");
                        }

                        return null;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    HardDeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Actions\RestoreDeletedBulkAction;
use App\Filament\Actions\SoftDeleteBulkAction;
use App\Filament\Actions\VerifyEmailBulkAction;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\DeletedColumn;
use App\Filament\Schemas\Columns\EmailColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\NameColumn;
use App\Filament\Schemas\Columns\OrderColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order_column')
            ->reorderable('order_column', direction: 'asc')
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : ''),
            )
            ->columns([
                OrderColumn::make(),
                IdColumn::make(),
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatar')
                    ->label(__('Avatar'))
                    ->conversion('thumb')
                    ->imageSize(40)
                    ->circular(),
                NameColumn::make(),
                EmailColumn::make(),
                IconColumn::make('email_verified_at')
                    ->label(__('Email verified at')),
                TextColumn::make('roles.name')
                    ->label(__('Role'))
                    ->badge()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Role::select('roles.name')
                                ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
                                ->whereColumn('model_has_roles.model_id', 'users.id')
                                ->where('model_has_roles.model_type', User::class)
                                ->limit(1),
                            $direction,
                        );
                    }),
                CreatedColumn::make(),
                UpdatedColumn::make(),
                DeletedColumn::make(),
            ])
            ->filters([
                Filter::make('verified users')
                    ->label(__('Verified users'))
                    ->toggle()
                    // Make sure the verified users filter is mutually exclusive with the unverified users filter
                    ->modifyFormFieldUsing(fn (Toggle $field) => $field
                        ->live()
                        ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../unverified users.isActive', false) : null),
                    )
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Filter::make('unverified users')
                    ->label(__('Unverified users'))
                    ->toggle()
                    // Make sure the unverified users filter is mutually exclusive with the verified users filter
                    ->modifyFormFieldUsing(fn (Toggle $field) => $field
                        ->live()
                        ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../verified users.isActive', false) : null),
                    )
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
                SelectFilter::make('role')
                    ->label(__('Role'))
                    ->relationship('roles', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    VerifyEmailBulkAction::make(),
                    SoftDeleteBulkAction::make(),
                    HardDeleteBulkAction::make(),
                    RestoreDeletedBulkAction::make(),
                ]),
            ]);
    }
}

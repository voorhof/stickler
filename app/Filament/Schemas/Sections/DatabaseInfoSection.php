<?php

namespace App\Filament\Schemas\Sections;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\FormatsJsonState;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class DatabaseInfoSection
{
    use FormatsJsonState;

    public static function make(): Section
    {
        return Section::make(__('Database Info'))
            ->icon(Heroicon::OutlinedInformationCircle)
            ->hiddenOn('create')
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->schema([
                Grid::make(4)->schema([
                    Grid::make()
                        ->columnSpanFull()
                        ->schema([
                            TextEntry::make('id')
                                ->label(__('ID'))
                                ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'id')),
                            TextEntry::make('slug')
                                ->label(__('Slug URL'))
                                ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'slug') || ! static::isMissingOrEmpty($record, 'url_slug')),
                        ]),
                    TextEntry::make('locale')
                        ->label(__('Language'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'locale'))
                        ->columnSpanFull(),
                    TextEntry::make('owner_type')
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'owner_type'))
                        ->label(__('Owner Type')),
                    TextEntry::make('owner_id')
                        ->label(__('Owner ID'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'owner_id'))
                        ->numeric(),
                    IconEntry::make('read')
                        ->label(__('Read by admin'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'read')),
                    IconEntry::make('replied')
                        ->label(__('Replied by admin'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'replied')),
                    TextEntry::make('replied_at')
                        ->label(__('Replied at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'replied_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('source')
                        ->label(__('Origin source'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'source')),
                    TextEntry::make('model_type')
                        ->label(__('Model type'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'model_type')),
                    TextEntry::make('model_id')
                        ->label(__('Model ID'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'model_id')),
                    TextEntry::make('file_name')
                        ->label(__('File name'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'file_name'))
                        ->columnSpanFull(),
                    TextEntry::make('mime_type')
                        ->label(__('Mime type'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'mime_type'))
                        ->columnSpanFull(),
                    TextEntry::make('disk')
                        ->label(__('Disk'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'disk')),
                    TextEntry::make('conversions_disk')
                        ->label(__('Conversions disk'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'conversions_disk')),
                    TextEntry::make('size')
                        ->label(__('Size'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'size')),
                    TextEntry::make('manipulations')
                        ->label(__('Manipulations'))
                        ->prose()
                        ->markdown()
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'manipulations'))
                        // Return ONE string so Filament does not iterate per element
                        ->state(fn ($record): ?string => static::formatJsonState($record->manipulations))
                        ->columnSpanFull(),
                    TextEntry::make('custom_properties')
                        ->label(__('Custom properties'))
                        ->prose()
                        ->markdown()
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'custom_properties'))
                        ->state(fn ($record): ?string => static::formatJsonState($record->custom_properties))
                        ->columnSpanFull(),
                    TextEntry::make('generated_conversions')
                        ->label(__('Generated conversions'))
                        ->prose()
                        ->markdown()
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'generated_conversions'))
                        ->state(fn ($record): ?string => static::formatJsonState($record->generated_conversions))
                        ->columnSpanFull(),
                    TextEntry::make('responsive_images')
                        ->label(__('Responsive images'))
                        ->prose()
                        ->markdown()
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'responsive_images'))
                        ->state(fn ($record): ?string => static::formatJsonState($record->responsive_images))
                        ->columnSpanFull(),
                    TextEntry::make('order_column')
                        ->label(__('Order column'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'order_column'))
                        ->columnSpanFull(),
                    TextEntry::make('created_at')
                        ->label(__('Created at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'created_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('updated_at')
                        ->label(__('Updated at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'updated_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('email_verified_at')
                        ->label(__('Email verified at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'email_verified_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('published_at')
                        ->label(__('Published at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'published_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('completed_at')
                        ->label(__('Completed at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'completed_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('archived_at')
                        ->label(__('Archived at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'archived_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    TextEntry::make('deleted_at')
                        ->label(__('Deleted at'))
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'deleted_at'))
                        ->dateTime('Y-m-d H:i:s'),
                    Grid::make(4)
                        ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'created_by_user_id') || static::isMissingOrEmpty($record, 'updated_by_user_id'))
                        ->columnSpanFull()
                        ->schema([
                            TextEntry::make('creator.name')
                                ->label(__('Created by'))
                                ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'created_by_user_id'))
                                ->formatStateUsing(fn (string $state, $record): string => "$state (ID: $record->created_by_user_id)")
                                ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record->creator]))
                                ->color('primary'),
                            TextEntry::make('updater.name')
                                ->label(__('Updated by'))
                                ->hidden(fn ($record) => static::isMissingOrEmpty($record, 'updated_by_user_id'))
                                ->formatStateUsing(fn (string $state, $record): string => "$state (ID: $record->updated_by_user_id)")
                                ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record->updater]))
                                ->color('primary'),
                        ]),
                ]),
            ]);
    }

    /**
     * Determine whether an attribute is absent from the model or empty.
     *
     * Accessing missing attributes throws when Eloquent strict mode is enabled.
     */
    protected static function isMissingOrEmpty(?Model $record, string $attribute): bool
    {
        if ($record === null) {
            return true;
        }

        $attributes = $record->getAttributes();

        if (! array_key_exists($attribute, $attributes)) {
            return true;
        }

        return blank($attributes[$attribute]);
    }
}

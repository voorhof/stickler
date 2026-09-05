<?php

namespace App\Filament\Pages\Spatie\Settings;

use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Traits\HasSettingsPageDefaults;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class General extends SettingsPage
{
    use HasSettingsPageDefaults;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Cog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static ?int $navigationSort = 9100; // Specify the order in which navigation items are listed.

    public static function getNavigationLabel(): string
    {
        return __('General settings');
    }

    public function getTitle(): string
    {
        return __('General settings');
    }

    protected static ?string $slug = 'general-settings';

    protected function getLogname(): string
    {
        return 'general_settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make(__('Contact details'))
                            ->icon(Heroicon::OutlinedHomeModern)
                            ->collapsible()
                            ->persistCollapsed()
                            ->inlineLabel()
                            ->schema([
                                TextInput::make('contact_name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_address')
                                    ->label(__('Address'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_city')
                                    ->label(__('City'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_country')
                                    ->label(__('Country'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_email')
                                    ->label(__('Email address'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_phone')
                                    ->label(__('Phone'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_company_name')
                                    ->label(__('Company name'))
                                    ->required()
                                    ->maxLength(250),
                                TextInput::make('contact_company_number')
                                    ->label(__('Company registration number'))
                                    ->required()
                                    ->maxLength(250),
                            ]),
                        Section::make(__('Social media links'))
                            ->icon(Heroicon::OutlinedLink)
                            ->collapsible()
                            ->persistCollapsed()
                            ->inlineLabel()
                            ->schema([
                                TextInput::make('social_facebook')
                                    ->label(__('Finished at'))
                                    ->maxLength(250),
                                TextInput::make('social_instagram')
                                    ->label(__('Instagram'))
                                    ->maxLength(250),
                                TextInput::make('social_linkedin')
                                    ->label(__('LinkedIn'))
                                    ->maxLength(250),
                            ]),
                        ActivityHistorySection::make(
                            state: fn () => app(GeneralSettings::class)->activities()->latest()->get(),
                        )->hidden(fn () => ! app(GeneralSettings::class)->activities()->count()),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

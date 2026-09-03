<?php

namespace App\Filament\Pages\Spatie\Settings;

use App\Filament\Pages\Spatie\Settings\Components\TermsRichText;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Traits\HasSettingsPageDefaults;
use App\Settings\TermsSettings;
use BackedEnum;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Terms extends SettingsPage
{
    use HasSettingsPageDefaults;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentText;

    protected static string $settings = TermsSettings::class;

    protected static ?int $navigationSort = 9120; // Specify the order in which navigation items are listed.

    public static function getNavigationLabel(): string
    {
        return __('Terms and policies');
    }

    public function getTitle(): string
    {
        return __('Terms and policies');
    }

    protected static ?string $slug = 'terms-settings';

    protected function getLogname(): string
    {
        return 'terms_settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make(__('Terms and conditions'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->schema([
                                TermsRichText::make(
                                    field: 'terms_and_conditions',
                                    label: 'Terms and conditions'),
                            ]),
                        Section::make(__('Privacy policy'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->schema([
                                TermsRichText::make(
                                    field: 'privacy_policy',
                                    label: 'Privacy policy'),
                            ]),
                        Section::make(__('Cookie policy'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->schema([
                                TermsRichText::make(
                                    field: 'cookie_policy',
                                    label: 'Cookie policy'),
                            ]),

                        ActivityHistorySection::make(
                            state: fn () => app(TermsSettings::class)->activities()->latest()->get(),
                        )->hidden(fn () => ! app(TermsSettings::class)->activities()->count()),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

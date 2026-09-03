<?php

namespace App\Filament\Pages\Spatie\Settings\Components;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;

class TermsRichText
{
    public static function make(
        string $field,
        string $label,
    ): RichEditor {
        return RichEditor::make($field)
            ->hiddenLabel()
            ->label(__($label))
            ->required()
            ->toolbarButtons([
                ['undo', 'redo'],
                [ToolbarButtonGroup::make(__('Paragraph'), ['paragraph', 'h1', 'h2', 'h3', 'h4'])],
                ['bold', 'italic', 'underline', 'link'],
                [ToolbarButtonGroup::make('Lists', ['bulletList', 'orderedList'])],
            ])
            ->floatingToolbars([
                'paragraph' => ['bold', 'italic', 'underline', 'link'],
                'heading' => ['h1', 'h2', 'h3', 'h4'],
            ])
            ->columnSpanFull();
    }
}

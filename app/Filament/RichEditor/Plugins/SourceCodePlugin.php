<?php

namespace App\Filament\RichEditor\Plugins;

use Dom\HTMLDocument;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Tiptap\Core\Extension;

/**
 * Adds a "Source code" tool to the rich editor to view and edit the HTML of the content.
 *
 * Based on the SourceCodePlugin of https://github.com/awcodes/richer-editor.
 * Note: on submit, TipTap only keeps the HTML its schema knows (unknown tags and attributes are dropped).
 */
class SourceCodePlugin implements RichContentPlugin
{
    public const string NAME = 'sourceCode';

    protected const string EMPTY_CONTENT = '<p></p>';

    protected Width $modalWidth = Width::FiveExtraLarge;

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make(self::NAME)
                ->label(__('Source code'))
                // Read the HTML from the live JS editor, so unsynced changes are included
                ->action(arguments: '{ source: $getEditor().getHTML() }')
                ->icon(Heroicon::OutlinedCodeBracketSquare),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make(self::NAME)
                ->label(__('Source code'))
                ->modalHeading(__('Source code'))
                ->modalWidth(fn (): Width => $this->getModalWidth())
                ->fillForm(fn (array $arguments): array => [
                    'source' => $this->formatHtml($arguments['source'] ?? null),
                ])
                ->schema([
                    CodeEditor::make('source')
                        ->hiddenLabel()
                        ->language(Language::Html)
                        ->wrap(),
                ])
                ->action(function (array $data, RichEditor $component): void {
                    $component->runCommands([
                        EditorCommand::make('setContent', arguments: [
                            filled($data['source'] ?? null) ? $data['source'] : self::EMPTY_CONTENT,
                        ]),
                    ]);
                })
                ->stickyModalFooter(),
        ];
    }

    public function modalWidth(Width $width): static
    {
        $this->modalWidth = $width;

        return $this;
    }

    public function getModalWidth(): Width
    {
        return $this->modalWidth;
    }

    /**
     * Put every top-level node of the HTML on its own line, for readability.
     */
    public function formatHtml(?string $html): string
    {
        if (blank($html)) {
            return self::EMPTY_CONTENT;
        }

        $document = HTMLDocument::createFromString(
            '<!DOCTYPE html><html lang="en"><body>'.$html.'</body></html>',
            LIBXML_NOERROR,
            'UTF-8',
        );

        $lines = [];

        foreach ($document->body->childNodes as $node) {
            if ($node instanceof Text && blank($node->textContent)) {
                continue;
            }

            $lines[] = $document->saveHtml($node);
        }

        return implode("\n", $lines);
    }
}

<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\RichEditor\Plugins\SourceCodePlugin;
use Filament\Support\Enums\Width;

describe('html formatting', function () {
    it('puts every top-level node on its own line', function () {
        $html = '<h2>Title</h2><p>Some <strong>bold</strong> text</p><ul><li><p>Item</p></li></ul>';

        expect(SourceCodePlugin::make()->formatHtml($html))
            ->toBe("<h2>Title</h2>\n<p>Some <strong>bold</strong> text</p>\n<ul><li><p>Item</p></li></ul>");
    });

    it('keeps multibyte characters and attributes intact', function () {
        $html = '<p>Café € ✓</p><img src="https://example.com/a.png" alt="Alt" data-id="12">';

        expect(SourceCodePlugin::make()->formatHtml($html))
            ->toBe("<p>Café € ✓</p>\n<img src=\"https://example.com/a.png\" alt=\"Alt\" data-id=\"12\">");
    });

    it('returns an empty paragraph for blank content', function (?string $html) {
        expect(SourceCodePlugin::make()->formatHtml($html))->toBe('<p></p>');
    })->with([null, '', '   ']);
});

describe('plugin configuration', function () {
    it('provides the source code tool and action', function () {
        $plugin = SourceCodePlugin::make();

        expect($plugin->getEditorTools())->toHaveCount(1)
            ->and($plugin->getEditorTools()[0]->getName())->toBe(SourceCodePlugin::NAME)
            ->and($plugin->getEditorActions())->toHaveCount(1)
            ->and($plugin->getEditorActions()[0]->getName())->toBe(SourceCodePlugin::NAME)
            ->and($plugin->getTipTapPhpExtensions())->toBeEmpty()
            ->and($plugin->getTipTapJsExtensions())->toBeEmpty();
    });

    it('has a configurable modal width', function () {
        expect(SourceCodePlugin::make()->getModalWidth())->toBe(Width::FiveExtraLarge)
            ->and(SourceCodePlugin::make()->modalWidth(Width::ScreenLarge)->getModalWidth())->toBe(Width::ScreenLarge);
    });
});

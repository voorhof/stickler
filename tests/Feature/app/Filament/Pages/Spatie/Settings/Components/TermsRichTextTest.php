<?php

use App\Filament\Pages\Spatie\Settings\Components\TermsRichText;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

test('it creates terms rich text component with correct configuration', function () {
    $livewire = new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;

        public function render(): string
        {
            return '<div></div>';
        }
    };

    $schema = Schema::make($livewire);
    $component = TermsRichText::make('terms_and_conditions', 'Terms and conditions')->container($schema);

    expect($component)->toBeInstanceOf(RichEditor::class)
        ->and($component->getName())->toBe('terms_and_conditions')
        ->and($component->isLabelHidden())->toBeTrue()
        ->and($component->getLabel())->toBe(__('Terms and conditions'))
        ->and($component->isRequired())->toBeTrue()
        ->and($component->getColumnSpan('default'))->toBe('full')
        ->and($component->hasToolbarButton('undo'))->toBeTrue()
        ->and($component->hasToolbarButton('redo'))->toBeTrue()
        ->and($component->hasToolbarButton('bold'))->toBeTrue()
        ->and($component->hasToolbarButton('italic'))->toBeTrue()
        ->and($component->hasToolbarButton('underline'))->toBeTrue()
        ->and($component->hasToolbarButton('link'))->toBeTrue()
        ->and($component->hasToolbarButton('paragraph'))->toBeTrue()
        ->and($component->hasToolbarButton('h1'))->toBeTrue()
        ->and($component->hasToolbarButton('h2'))->toBeTrue()
        ->and($component->hasToolbarButton('h3'))->toBeTrue()
        ->and($component->hasToolbarButton('h4'))->toBeTrue()
        ->and($component->hasToolbarButton('bulletList'))->toBeTrue()
        ->and($component->hasToolbarButton('orderedList'))->toBeTrue();
});

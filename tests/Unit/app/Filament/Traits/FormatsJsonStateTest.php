<?php

use App\Filament\Traits\FormatsJsonState;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->formatter = new class
    {
        use FormatsJsonState;

        public function format(mixed $state): ?string
        {
            return self::formatJsonState($state);
        }
    };
});

it('returns null when the state is empty', function () {
    expect($this->formatter->format(null))->toBeNull()
        ->and($this->formatter->format([]))->toBeNull()
        ->and($this->formatter->format(new Collection))->toBeNull();
});

it('hides keys with empty values but keeps populated ones', function () {
    $state = [
        'tags' => null,
        'categories' => ['event categorie', 'itaque', 'quasi'],
        'brands' => null,
        'old_categories' => ['itaque', 'quasi'],
    ];

    $output = $this->formatter->format($state);

    expect($output)->toContain('**Categories**')
        ->and($output)->toContain('- event categorie')
        ->and($output)->toContain('**Old categories**')
        ->and($output)->not->toContain('Tags')
        ->and($output)->not->toContain('Brands');
});

it('renders associative scalar values as key value pairs', function () {
    $state = [
        'attributes' => [
            'address' => 'De Costerlaan 300',
            'birthday' => '1976-12-15',
            'gender' => 'male',
            'order_column' => 2,
        ],
    ];

    $output = $this->formatter->format($state);

    expect($output)->toContain('**Attributes**')
        ->and($output)->toContain('- **Address:** De Costerlaan 300')
        ->and($output)->toContain('- **Gender:** male')
        ->and($output)->toContain('- **Order column:** 2')
        ->and($output)->not->toContain('- 0');
});

it('formats booleans as Yes and No and keeps false values', function () {
    $state = [
        'thumb' => true,
        'preview' => false,
    ];

    $output = $this->formatter->format($state);

    expect($output)->toContain('- **Thumb:** '.__('Yes'))
        ->and($output)->toContain('- **Preview:** '.__('No'));
});

it('normalizes a JSON string into formatted markdown', function () {
    $state = '{"categories":["itaque","quasi"]}';

    $output = $this->formatter->format($state);

    expect($output)->toContain('**Categories**')
        ->and($output)->toContain('- itaque')
        ->and($output)->toContain('- quasi');
});

it('normalizes a collection cast into formatted markdown', function () {
    $state = new Collection([
        'categories' => ['itaque', 'quasi'],
    ]);

    $output = $this->formatter->format($state);

    expect($output)->toContain('**Categories**')
        ->and($output)->toContain('- itaque');
});

it('truncates the base64svg value', function () {
    $long = str_repeat('a', 100);

    $output = $this->formatter->format(['base64svg' => $long]);

    expect($output)->toContain('...')
        ->and(mb_strlen($output))->toBeLessThan(mb_strlen('- **Base64svg:** '.$long));
});

it('does not truncate other long scalar values', function () {
    $long = str_repeat('a', 100);

    $output = $this->formatter->format(['content' => $long]);

    expect($output)->toContain($long)
        ->and($output)->not->toContain('...');
});

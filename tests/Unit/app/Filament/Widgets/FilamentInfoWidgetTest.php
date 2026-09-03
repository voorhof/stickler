<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Widgets\FilamentInfoWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();
});

it('can be viewed when authenticated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(FilamentInfoWidget::canView())->toBeTrue();

    livewire(FilamentInfoWidget::class)->assertOk();
});

it('cannot be viewed when unauthenticated', function () {
    expect(FilamentInfoWidget::canView())->toBeFalse();
});

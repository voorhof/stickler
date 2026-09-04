<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Widgets\AccountWidget;
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

    expect(AccountWidget::canView())->toBeTrue();

    livewire(AccountWidget::class)->assertOk();
});

it('cannot be viewed when unauthenticated', function () {
    expect(AccountWidget::canView())->toBeFalse();
});

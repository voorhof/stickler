<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Widgets\AccountWidget;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();
});

describe('widget visibility', function () {
    it('can be viewed when authenticated', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        expect(AccountWidget::canView())->toBeTrue();

        livewire(AccountWidget::class)->assertOk();
    });

    it('cannot be viewed when unauthenticated', function () {
        expect(AccountWidget::canView())->toBeFalse();
    });
});

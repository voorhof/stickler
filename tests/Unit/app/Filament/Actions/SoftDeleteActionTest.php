<?php

use App\Filament\Actions\SoftDeleteAction;
use App\Models\Media;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has the correct default name', function () {
    $action = SoftDeleteAction::make();
    expect($action->getName())->toBe('softDelete');
});

it('is hidden if the record is soft deleted', function () {
    $action = SoftDeleteAction::make();
    $message = Message::factory()->softDeleted()->create();

    // Set the record on the action
    $action->record($message);

    // The hidden() callback should evaluate to true
    expect($action->isHidden())->toBeTrue();
});

it('is not hidden if the record is not soft deleted', function () {
    $action = SoftDeleteAction::make();
    $message = Message::factory()->create();

    // Set the record on the action
    $action->record($message);

    // The hidden() callback should evaluate to false
    expect($action->isHidden())->toBeFalse();
});

it('is not hidden if the record can not be soft deleted', function () {
    $action = SoftDeleteAction::make();
    $media = Media::create([
        'name' => 'Mountains',
        'model_type' => 'App\Models\User',
        'model_id' => 1,
        'collection_name' => 'avatar',
        'file_name' => '01KT6F4AYDEDF585Q9Q1TJZWM8.jpg',
        'disk' => 'media',
        'size' => 66438,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    // Set the record on the action
    $action->record($media);

    // The hidden() callback should evaluate to false
    expect($action->isHidden())->toBeFalse();
});

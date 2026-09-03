<?php

use App\Support\MediaLibrary\CustomPathGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

test('it generates correct path without prefix', function () {
    config()->set('media-library.prefix', '');

    $media = new Media;
    $media->forceFill([
        'id' => 123,
        'created_at' => Carbon::parse('2026-08-01 12:00:00'),
    ]);

    $generator = new CustomPathGenerator;

    expect($generator->getPath($media))->toBe('123/')
        ->and($generator->getPathForConversions($media))->toBe('123/conversions/')
        ->and($generator->getPathForResponsiveImages($media))->toBe('123/responsive-images/');
});

test('it generates correct path with prefix and formatted year', function () {
    config()->set('media-library.prefix', 'uploads');

    $media = new Media;
    $media->forceFill([
        'id' => 456,
        'created_at' => Carbon::parse('2025-05-15 10:30:00'),
    ]);

    $generator = new CustomPathGenerator;

    expect($generator->getPath($media))->toBe('uploads/2025/456/')
        ->and($generator->getPathForConversions($media))->toBe('uploads/2025/456/conversions/')
        ->and($generator->getPathForResponsiveImages($media))->toBe('uploads/2025/456/responsive-images/');
});

test('it works correctly with media model instance', function () {
    config()->set('media-library.prefix', '');

    $media = new Media([
        'model_type' => 'App\Models\User',
        'model_id' => 1,
        'collection_name' => 'avatar',
        'name' => 'avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 100,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
    $media->forceFill([
        'id' => 789,
        'created_at' => Carbon::parse('2026-01-01 00:00:00'),
    ]);

    $generator = new CustomPathGenerator;

    expect($generator->getPath($media))->toBe('789/')
        ->and($generator->getPathForConversions($media))->toBe('789/conversions/')
        ->and($generator->getPathForResponsiveImages($media))->toBe('789/responsive-images/');
});

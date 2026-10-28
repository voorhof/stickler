<?php

/** @noinspection PhpCaseWithValueNotFoundInEnumInspection, PhpMultipleClassDeclarationsInspection */

use App\Enums\OrderStatus;

test('order status has all expected cases', function () {
    expect(OrderStatus::cases())->toHaveCount(6);
});

test('order status cases have correct values', function () {
    expect(OrderStatus::Pending->value)->toBe('pending')
        ->and(OrderStatus::Confirmed->value)->toBe('confirmed')
        ->and(OrderStatus::Processing->value)->toBe('processing')
        ->and(OrderStatus::Shipped->value)->toBe('shipped')
        ->and(OrderStatus::Delivered->value)->toBe('delivered')
        ->and(OrderStatus::Cancelled->value)->toBe('cancelled');
});

test('order status can be created from valid string', function () {
    expect(OrderStatus::from('pending'))->toBe(OrderStatus::Pending)
        ->and(OrderStatus::from('confirmed'))->toBe(OrderStatus::Confirmed)
        ->and(OrderStatus::from('processing'))->toBe(OrderStatus::Processing)
        ->and(OrderStatus::from('shipped'))->toBe(OrderStatus::Shipped)
        ->and(OrderStatus::from('delivered'))->toBe(OrderStatus::Delivered)
        ->and(OrderStatus::from('cancelled'))->toBe(OrderStatus::Cancelled);
});

test('order status tryFrom returns enum for valid value', function () {
    expect(OrderStatus::tryFrom('pending'))->toBe(OrderStatus::Pending)
        ->and(OrderStatus::tryFrom('confirmed'))->toBe(OrderStatus::Confirmed);
});

test('order status tryFrom returns null for invalid value', function () {
    expect(OrderStatus::tryFrom('unknown'))->toBeNull()
        ->and(OrderStatus::tryFrom(''))->toBeNull();
});

test('order status from throws exception for invalid value', function () {
    expect(fn () => OrderStatus::from('unknown'))->toThrow(ValueError::class);
});

test('order status is backed string enum', function () {
    expect(OrderStatus::Pending)->toBeInstanceOf(BackedEnum::class)
        ->and(OrderStatus::Pending->value)->toBeString();
});

test('order status cases are unique', function () {
    $values = array_map(fn ($case) => $case->value, OrderStatus::cases());
    expect($values)->toHaveCount(count(array_unique($values)));
});

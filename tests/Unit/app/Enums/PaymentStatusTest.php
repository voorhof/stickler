<?php

/** @noinspection PhpCaseWithValueNotFoundInEnumInspection, PhpMultipleClassDeclarationsInspection */

use App\Enums\PaymentStatus;

test('payment status has all expected cases', function () {
    expect(PaymentStatus::cases())->toHaveCount(3);
});

test('payment status cases have correct values', function () {
    expect(PaymentStatus::Unpaid->value)->toBe('unpaid')
        ->and(PaymentStatus::Paid->value)->toBe('paid')
        ->and(PaymentStatus::Refunded->value)->toBe('refunded');
});

test('payment status can be created from valid string', function () {
    expect(PaymentStatus::from('unpaid'))->toBe(PaymentStatus::Unpaid)
        ->and(PaymentStatus::from('paid'))->toBe(PaymentStatus::Paid)
        ->and(PaymentStatus::from('refunded'))->toBe(PaymentStatus::Refunded);
});

test('payment status tryFrom returns enum for valid value', function () {
    expect(PaymentStatus::tryFrom('unpaid'))->toBe(PaymentStatus::Unpaid)
        ->and(PaymentStatus::tryFrom('paid'))->toBe(PaymentStatus::Paid);
});

test('payment status tryFrom returns null for invalid value', function () {
    expect(PaymentStatus::tryFrom('unknown'))->toBeNull()
        ->and(PaymentStatus::tryFrom(''))->toBeNull();
});

test('payment status from throws exception for invalid value', function () {
    expect(fn () => PaymentStatus::from('unknown'))->toThrow(ValueError::class);
});

test('payment status is backed string enum', function () {
    expect(PaymentStatus::Unpaid)->toBeInstanceOf(BackedEnum::class)
        ->and(PaymentStatus::Unpaid->value)->toBeString();
});

test('payment status cases are unique', function () {
    $values = array_map(fn ($case) => $case->value, PaymentStatus::cases());
    expect($values)->toHaveCount(count(array_unique($values)));
});

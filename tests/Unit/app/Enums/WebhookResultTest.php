<?php

/** @noinspection PhpCaseWithValueNotFoundInEnumInspection, PhpMultipleClassDeclarationsInspection */

use App\Enums\WebhookResult;

test('webhook result has all expected cases', function () {
    expect(WebhookResult::cases())->toHaveCount(5);
});

test('webhook result cases have correct values', function () {
    expect(WebhookResult::Success->value)->toBe('success')
        ->and(WebhookResult::BadPayload->value)->toBe('bad_payload')
        ->and(WebhookResult::NotFound->value)->toBe('not_found')
        ->and(WebhookResult::SignatureInvalid->value)->toBe('signature_invalid')
        ->and(WebhookResult::Error->value)->toBe('error');
});

test('webhook result can be created from valid string', function () {
    expect(WebhookResult::from('success'))->toBe(WebhookResult::Success)
        ->and(WebhookResult::from('bad_payload'))->toBe(WebhookResult::BadPayload)
        ->and(WebhookResult::from('not_found'))->toBe(WebhookResult::NotFound)
        ->and(WebhookResult::from('signature_invalid'))->toBe(WebhookResult::SignatureInvalid)
        ->and(WebhookResult::from('error'))->toBe(WebhookResult::Error);
});

test('webhook result tryFrom returns enum for valid value', function () {
    expect(WebhookResult::tryFrom('success'))->toBe(WebhookResult::Success)
        ->and(WebhookResult::tryFrom('error'))->toBe(WebhookResult::Error);
});

test('webhook result tryFrom returns null for invalid value', function () {
    expect(WebhookResult::tryFrom('unknown'))->toBeNull()
        ->and(WebhookResult::tryFrom(''))->toBeNull();
});

test('webhook result from throws exception for invalid value', function () {
    expect(fn () => WebhookResult::from('unknown'))->toThrow(ValueError::class);
});

test('webhook result is backed string enum', function () {
    expect(WebhookResult::Success)->toBeInstanceOf(BackedEnum::class)
        ->and(WebhookResult::Success->value)->toBeString();
});

test('webhook result cases are unique', function () {
    $values = array_map(fn ($case) => $case->value, WebhookResult::cases());
    expect($values)->toHaveCount(count(array_unique($values)));
});

test('webhook result http status codes are correct', function () {
    expect(WebhookResult::Success->httpStatusCode())->toBe(200)
        ->and(WebhookResult::BadPayload->httpStatusCode())->toBe(400)
        ->and(WebhookResult::NotFound->httpStatusCode())->toBe(404)
        ->and(WebhookResult::SignatureInvalid->httpStatusCode())->toBe(400)
        ->and(WebhookResult::Error->httpStatusCode())->toBe(500);
});

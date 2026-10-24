<?php

describe('application health', function () {
    test('the application returns a successful response', function () {
        $response = $this->get('/up');

        $response->assertOk();
    });
});

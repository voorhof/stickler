<?php

describe('brandbook page', function () {
    test('it can view the brandbook page', function () {
        $response = $this->get(route('brandbook.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.brandbook');
    });
});

<?php

test('it can view the welcome page', function () {
    $response = $this->get(route('welcome.index'));

    $response->assertSuccessful()
        ->assertViewIs('public.welcome');
});

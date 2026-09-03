<?php

use App\Settings\GeneralSettings;
use App\Settings\TermsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can view the general terms page', function () {
    $response = $this->get(route('terms.terms'));

    $response->assertSuccessful()
        ->assertViewIs('public.terms')
        ->assertSeeHtml(app(TermsSettings::class)->terms_and_conditions)

        // These assertions are the same values on all terms pages, so we only test them once here
        ->assertSeeText('Contactgegevens')
        ->assertSeeText('David Carton')
        ->assertSeeText(app(GeneralSettings::class)->contact_email)
        ->assertSeeText(app(GeneralSettings::class)->contact_phone)
        ->assertSeeText(app(GeneralSettings::class)->contact_address)
        ->assertSeeText(app(GeneralSettings::class)->contact_city)
        ->assertSeeText(app(GeneralSettings::class)->contact_country)
        ->assertSeeText(app(GeneralSettings::class)->contact_company_name)
        ->assertSeeText(app(GeneralSettings::class)->contact_company_number)
        ->assertSeeText(config('app.url').'/contact');
});

test('it can view the privacy policy page', function () {
    $response = $this->get(route('terms.privacy'));

    $response->assertSuccessful()
        ->assertViewIs('public.terms')
        ->assertSeeHtml(app(TermsSettings::class)->privacy_policy);
});

test('it can view the cookie policy page page', function () {
    $response = $this->get(route('terms.cookie'));

    $response->assertSuccessful()
        ->assertViewIs('public.terms')
        ->assertSeeHtml(app(TermsSettings::class)->cookie_policy);
});

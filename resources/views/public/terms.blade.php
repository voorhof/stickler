@php use App\Settings\GeneralSettings; @endphp
<x-layouts.app>
    <x-slot:headTitle>{{ $headTitle }}</x-slot>

    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-md-11 col-lg-9 col-xxl-8 mb-4">
                    <div class="c-terms">
                        {!! $content !!}
                    </div>

                    <hr class="my-4">

                    <h2>
                        Contactgegevens
                    </h2>

                    <address>
                        <p class="lead pt-2">
                            {{ app(GeneralSettings::class)->contact_name }}
                            <br>
                            <a class="text-decoration-none"
                               href="mailto:{{ app(GeneralSettings::class)->contact_email }}">
                                {{ app(GeneralSettings::class)->contact_email }}
                            </a>
                            <br>
                            <a class="text-decoration-none"
                               href="tel:{{ str_replace(' ', '', app(GeneralSettings::class)->contact_phone) }}">
                                {{ app(GeneralSettings::class)->contact_phone }}
                            </a>
                        </p>

                        <p>
                            {{ app(GeneralSettings::class)->contact_address }}
                            <br>
                            {{ app(GeneralSettings::class)->contact_city }}
                            <br>
                            {{ app(GeneralSettings::class)->contact_country }}
                        </p>

                        <p>
                            {{ app(GeneralSettings::class)->contact_company_name }}
                            <br>
                            {{ app(GeneralSettings::class)->contact_company_number }}
                        </p>

                        <a href="{{ route('contact.index') }}">{{ config('app.url') }}/contact</a>
                    </address>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

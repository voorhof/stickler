@php use App\Settings\GeneralSettings; @endphp
<x-layouts.app>
    <x-slot:headTitle>{{ $headTitle }}</x-slot>

    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center mb-5">
                <div class="col-auto">
                    <h1>
                        CONTACT
                    </h1>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md ps-4 ps-xl-5 mb-5 mb-md-0">
                    <h2 class="mb-3">Contact details</h2>
                    <address class="lead">
                        <strong class="d-block fw-medium mb-1">
                            <x-icons.person />
                            {{ app(GeneralSettings::class)->contact_name }}
                        </strong>

                        <div class="mb-4">
                            <a href="mailto:{{ app(GeneralSettings::class)->contact_email }}"
                               class="link-body-emphasis text-decoration-none">
                                {{ app(GeneralSettings::class)->contact_email }}
                            </a>
                            <br>
                            <a href="tel:{{ str_replace(' ', '', app(GeneralSettings::class)->contact_phone) }}"
                               class="link-body-emphasis text-decoration-none">
                                {{ str_replace(' ', '', app(GeneralSettings::class)->contact_phone) }}
                            </a>
                        </div>

                        <div class="mb-4">
                            <strong class="d-block fw-medium mb-1">
                                <x-icons.building />
                                {{ app(GeneralSettings::class)->contact_company_name }}
                            </strong>

                            <span>{{ app(GeneralSettings::class)->contact_company_number }}</span>
                            <br>
                            <span>{{ app(GeneralSettings::class)->contact_address }}</span>
                            <br>
                            <span>{{ app(GeneralSettings::class)->contact_city }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-4">
                            <a href="{{ app(GeneralSettings::class)->social_facebook }}" target="_blank"
                               class="h2 m-0 link-body-emphasis text-decoration-none" aria-label="Facebook">
                                <x-icons.facebook />
                            </a>

                            <a href="{{ app(GeneralSettings::class)->social_instagram }}" target="_blank"
                               class="h2 m-0 link-body-emphasis text-decoration-none" aria-label="Instagram">
                                <x-icons.instagram />
                            </a>

                            <a href="{{ app(GeneralSettings::class)->social_linkedin }}" target="_blank"
                               class="h2 m-0 link-body-emphasis text-decoration-none" aria-label="LinkedIn">
                                <x-icons.linkedin />
                            </a>
                        </div>
                    </address>
                </div>

                <div class="col-md px-4 px-xl-5 order-md-first">
                    @if(session('success'))
                        <div id="bedankt" class="pt-5">
                            <div class="alert alert-success text-center fw-medium mt-3">
                                {{ session('success') }}
                            </div>
                        </div>
                    @else
                        <h2 class="mb-4">Stel je vraag</h2>

                        <form action="{{ route('contact.store') }}" method="POST" class="c-contact-form">
                            <x-csrf-input />
                            <x-honeypot />

                            <div class="form-floating mb-3">
                                <input type="text"
                                       @class(['form-control', 'is-invalid' => $errors->has('name')])
                                       id="nameInput"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Jouw naam"
                                       autocomplete="name"
                                       required
                                       maxlength="250">
                                <label for="nameInput">@lang('Name')<span class="opacity-50">*</span></label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email"
                                       @class(['form-control', 'is-invalid' => $errors->has('email')])
                                       id="emailInput"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="naam@voorbeeld.be"
                                       autocomplete="email"
                                       required
                                       maxlength="250">
                                <label for="emailInput">@lang('Email')<span class="opacity-50">*</span></label>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text"
                                       @class(['form-control', 'is-invalid' => $errors->has('phone')])
                                       id="phoneInput"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="0123456789"
                                       autocomplete="tel"
                                       maxlength="250">
                                <label for="phoneInput">@lang('Phone')</label>
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text"
                                       @class(['form-control', 'is-invalid' => $errors->has('subject')])
                                       id="subjectInput"
                                       name="subject"
                                       value="{{ old('subject') }}"
                                       placeholder="Onderwerp"
                                       required
                                       maxlength="250">
                                <label for="subjectInput">@lang('Subject')<span class="opacity-50">*</span></label>
                                @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                            <textarea @class(['form-control c-contact-form__textarea', 'is-invalid' => $errors->has('message')])
                                      id="messageInput"
                                      name="message"
                                      placeholder="Jouw berichtje"
                                      required
                                      maxlength="2500"
                            >{{ old('message') }}</textarea>
                                <label for="messageInput">@lang('Message')<span class="opacity-50">*</span></label>
                                @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-primary" type="submit">@lang('Submit')</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

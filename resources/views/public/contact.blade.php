@php use App\Settings\GeneralSettings; @endphp
<x-layouts.app>
    <x-slot:headTitle>{{ $headTitle }}</x-slot>

    <section class="c-contact-section py-5">
        <div class="container-lg">
            <div class="row">
                <div class="col-md ps-4 ps-xl-5 mb-5 mb-md-0">
                    <h2 class="mb-3">Contact details</h2>
                    <address class="lead">
                        <strong class="d-block fw-medium mb-1">
                            <i class="ppi ppi-person"></i>
                            {{ app(GeneralSettings::class)->contact_name }}
                        </strong>

                        <div class="mb-4">
                            <a href="mailto:{{ app(GeneralSettings::class)->contact_email }}"
                               class="text-dark text-decoration-none">
                                {{ app(GeneralSettings::class)->contact_email }}
                            </a>
                            <br>
                            <a href="tel:{{ str_replace(' ', '', app(GeneralSettings::class)->contact_phone) }}"
                               class="text-dark text-decoration-none">
                                {{ str_replace('23', '2 3', str_replace('42', '4 2', str_replace('+32', '0', app(GeneralSettings::class)->contact_phone))) }}
                            </a>
                        </div>

                        <div class="mb-4">
                            <strong class="d-block fw-medium mb-1">
                                <i class="ppi ppi-building"></i>
                                {{ app(GeneralSettings::class)->contact_company_name }}
                            </strong>

                            <span>{{ app(GeneralSettings::class)->contact_company_number }}</span>
                            <br>
                            <span>{{ app(GeneralSettings::class)->contact_address }}</span>
                            <br>
                            <span>{{ app(GeneralSettings::class)->contact_city }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-4">
                            <a href="{{ app(GeneralSettings::class)->social_instagram }}" target="_blank"
                               class="h3 m-0 text-dark text-decoration-none" aria-label="Instagram">
                                <i class="ppi ppi-instagram"></i>
                            </a>

                            <a href="{{ app(GeneralSettings::class)->social_linkedin }}" target="_blank"
                               class="h4 m-0 text-dark text-decoration-none" aria-label="LinkedIn">
                                <i class="ppi ppi-linkedin"></i>
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
                                <label for="nameInput">@lang('Name')<span class="opacity-60">*</span></label>
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
                                <label for="emailInput">@lang('Email')<span class="opacity-60">*</span></label>
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
                                <label for="subjectInput">@lang('Subject')<span class="opacity-60">*</span></label>
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
                                <label for="messageInput">@lang('Message')<span class="opacity-60">*</span></label>
                                @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-dark" type="submit">Versturen</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container-lg">
            <div class="row align-items-end">
                <div class="col-md-6 px-4 px-xl-5 mb-4 mb-md-0">
                    <h2 class="mb-3">Kennismaking</h2>
                    <p>
                        Voordat je beslist of een nieuwe website van <span class="d-inline-block">Pietje Precies</span> de ideale oplossing voor je is,
                        gaan we natuurlijk altijd eerst even met elkaar om de tafel zitten.
                    </p>
                    <p>
                        Een gratis en vrijblijvend adviesgesprek waarin je informatie krijgt over onze aanpak
                        en de werking van het standaard CMS-systeem, aangevuld met een live-demo.
                    </p>
                    <p>
                        Neem contact op via het formulier, bel of stuur ons een e-mail en we plannen samen iets in.
                    </p>
                    <p class="mb-0">
                        Uiteraard onder het genot van een lekker kopje koffie.
                    </p>
                </div>

                <div class="col-md-6 ps-4 ps-xl-5">
                    <div class="c-coffee">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="22.95 15.31 155.1 88.38" role="img">
                            <path class="c-coffee__smoke" d="M42.95,68.06v-16.4s0,0,0,0h0c0-3.91,3.17-7.07,7.07-7.07h.19s12.32,0,12.32,0c3.91,0,7.07-3.17,7.07-7.07h0c0-3.91-3.17-7.07-7.07-7.07h.28s-12.79,0-12.79,0c-3.91,0-7.07-3.17-7.07-7.07h0c0-3.91,3.17-7.07,7.07-7.07h127.03"/>
                            <path class="c-coffee__cup" d="M67.54,70.92h-4.59v-7.23H22.95v40h40v-7.23h4.59c7.05,0,12.77-5.72,12.77-12.77s-5.72-12.77-12.77-12.77ZM67.54,88.47h-4.59v-9.55h4.59c2.63,0,4.77,2.14,4.77,4.77s-2.14,4.77-4.77,4.77Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

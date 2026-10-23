@php use App\Settings\GeneralSettings; @endphp
<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center mb-5">
                <div class="col-auto">
                    <h1>CONTACT</h1>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md ps-xl-5 mb-md-0 mb-5 ps-4">
                    <h2 class="mb-3">Contact details</h2>
                    <address class="lead">
                        <strong class="d-block fw-medium mb-1">
                            <x-icons.person />
                            {{ app(GeneralSettings::class)->contact_name }}
                        </strong>

                        <div class="mb-4">
                            <a
                                href="mailto:{{ app(GeneralSettings::class)->contact_email }}"
                                class="link-body-emphasis text-decoration-none"
                            >
                                {{ app(GeneralSettings::class)->contact_email }}
                            </a>
                            <br />
                            <a
                                href="tel:{{ str_replace(' ', '', app(GeneralSettings::class)->contact_phone) }}"
                                class="link-body-emphasis text-decoration-none"
                            >
                                {{ str_replace(' ', '', app(GeneralSettings::class)->contact_phone) }}
                            </a>
                        </div>

                        <div class="mb-4">
                            <strong class="d-block fw-medium mb-1">
                                <x-icons.building />
                                {{ app(GeneralSettings::class)->contact_company_name }}
                            </strong>

                            <span>{{ app(GeneralSettings::class)->contact_company_number }}</span>
                            <br />
                            <span>{{ app(GeneralSettings::class)->contact_address }}</span>
                            <br />
                            <span>{{ app(GeneralSettings::class)->contact_city }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-4">
                            @if (app(GeneralSettings::class)->social_facebook)
                                <a
                                    href="{{ app(GeneralSettings::class)->social_facebook }}"
                                    target="_blank"
                                    class="h2 link-body-emphasis text-decoration-none m-0"
                                    aria-label="Facebook"
                                >
                                    <x-icons.facebook />
                                </a>
                            @endif

                            @if (app(GeneralSettings::class)->social_instagram)
                                <a
                                    href="{{ app(GeneralSettings::class)->social_instagram }}"
                                    target="_blank"
                                    class="h2 link-body-emphasis text-decoration-none m-0"
                                    aria-label="Instagram"
                                >
                                    <x-icons.instagram />
                                </a>
                            @endif

                            @if (app(GeneralSettings::class)->social_linkedin)
                                <a
                                    href="{{ app(GeneralSettings::class)->social_linkedin }}"
                                    target="_blank"
                                    class="h2 link-body-emphasis text-decoration-none m-0"
                                    aria-label="LinkedIn"
                                >
                                    <x-icons.linkedin />
                                </a>
                            @endif
                        </div>
                    </address>
                </div>

                <div class="col-md px-xl-5 order-md-first px-4">
                    @if (session('success'))
                        <div id="successAlert" class="pt-5">
                            <div class="alert alert-success fw-medium mt-3 text-center">{{ session('success') }}</div>
                        </div>
                    @else
                        <h2 class="mb-4">Stel je vraag</h2>

                        <form action="{{ route('contact.store') }}" method="POST" class="c-contact-form">
                            <x-csrf-input />
                            <x-honeypot />

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    @class(['form-control', 'is-invalid' => $errors->has('name')])
                                    id="nameInput"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Jouw naam"
                                    autocomplete="name"
                                    required
                                    maxlength="250"
                                />
                                <label for="nameInput">
                                    @lang('Name')
                                    <span class="opacity-50">*</span>
                                </label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="email"
                                    @class(['form-control', 'is-invalid' => $errors->has('email')])
                                    id="emailInput"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@voorbeeld.be"
                                    autocomplete="email"
                                    required
                                    maxlength="250"
                                />
                                <label for="emailInput">
                                    @lang('Email')
                                    <span class="opacity-50">*</span></label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    @class(['form-control', 'is-invalid' => $errors->has('phone')])
                                    id="phoneInput"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="0123456789"
                                    autocomplete="tel"
                                    maxlength="250"
                                />
                                <label for="phoneInput">@lang('Phone')</label>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    @class(['form-control', 'is-invalid' => $errors->has('subject')])
                                    id="subjectInput"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    placeholder="Onderwerp"
                                    required
                                    maxlength="250"
                                />
                                <label for="subjectInput">
                                    @lang('Subject')
                                    <span class="opacity-50">*</span></label>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <textarea
                                    @class(['form-control c-contact-form__textarea', 'is-invalid' => $errors->has('message')])
                                    id="messageInput"
                                    name="message"
                                    placeholder="Jouw berichtje"
                                    required
                                    maxlength="2500"
                                >{{ old('message') }}</textarea>
                                <label for="messageInput">
                                    @lang('Message')
                                    <span class="opacity-50">*</span></label>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-primary" type="submit">@lang('Submit')</button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($openingHours)
                <div class="row border-top mb-5 pt-5">
                    <div class="col-md-auto ps-xl-5 ps-4">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="h4 mb-0">
                                    <span @class([
                                        'text-success' => $openingHours->isOpen(),
                                        'text-danger' => ! $openingHours->isOpen(),
                                        'fs-3',
                                    ])>●</span>
                                    {{ __('filament-business-hours::filament-business-hours.navigation.label') }}
                                </h2>
                            </div>
                            <div class="card-body p-0">
                                <table class="table">
                                    <tbody>
                                        @foreach ($weekdays as $day)
                                            @php
                                                $hoursForDay = $openingHours->forDay($day);
                                                $isToday = mb_strtolower(now()->format('l')) === $day;
                                            @endphp
                                            <tr @if ($isToday) class="table-active" @endif>
                                                <td>
                                                    <strong>{{ __('filament-business-hours::filament-business-hours.days.' . $day) }}</strong>
                                                    @if ($isToday)
                                                        <br />
                                                        <span class="badge bg-primary"
                                                            ><small
                                                                >{{ __('filament-query-builder::query-builder.operators.date.presets.today') }}</small
                                                            ></span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (count($hoursForDay) > 0)
                                                        @foreach ($hoursForDay as $range)
                                                            <div>{{ $range->format('H:i', ' %s - %s ') }}</div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">{{ __('filament-business-hours::filament-business-hours.grid.closed') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @push('scripts-body-bottom')
        <script>
            /* Disable submit button on form submit to prevent double submission */
            const contactForm = document.querySelector('.c-contact-form');

            if (contactForm) {
                contactForm.addEventListener('submit', function () {
                    const contactFormButton = contactForm.querySelector('button');
                    contactFormButton.disabled = true;
                });
            }

            /* Scroll to success message after form submit */
            const successAlert = document.getElementById('successAlert');

            if (successAlert) {
                successAlert.scrollIntoView();
            }
        </script>
    @endpush
</x-layouts.app>

<x-layouts.app>
    <x-authcard :cardTitle="__('Register')">
        <form method="POST" action="{{ route('register') }}" novalidate>
            <x-csrf-input />
            <x-honeypot />

            {{-- Name --}}
            <div class="form-floating mb-3">
                <x-form.input-text
                    id="name"
                    name="name"
                    :value="old('name')"
                    placeholder="{{ __('validation.attributes.name') }}"
                    required
                    autocomplete="name"
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                    :describedBy="$errors->has('name') ? 'name_error' : null"
                />
                <x-form.input-label for="name" :value="__('validation.attributes.name')" />
                <x-form.input-error id="name_error" :message="$errors->first('name')" />
            </div>

            {{-- Email Address --}}
            <div class="form-floating mb-3">
                <x-form.input-text
                    type="email"
                    id="email"
                    name="email"
                    :value="old('email')"
                    placeholder="{{ __('validation.attributes.email') }}"
                    required
                    autocomplete="username"
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    :describedBy="$errors->has('email') ? 'email_error' : null"
                />
                <x-form.input-label for="email" :value="__('validation.attributes.email')" />
                <x-form.input-error id="email_error" :message="$errors->first('email')" />
            </div>

            {{-- Password --}}
            <div class="form-floating mb-3">
                <x-form.input-text
                    type="password"
                    id="password"
                    name="password"
                    placeholder="{{ __('validation.attributes.password') }}"
                    required
                    autocomplete="new-password"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    :describedBy="$errors->has('password') ? 'password_error' : null"
                />
                <x-form.input-label for="password" :value="__('validation.attributes.password')" />
                <x-form.input-error id="password_error" :message="$errors->first('password')" />
            </div>

            {{-- Confirm Password --}}
            <div class="form-floating mb-3">
                <x-form.input-text
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="{{ __('validation.attributes.password_confirmation') }}"
                    required
                    autocomplete="new-password"
                    class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                    :describedBy="$errors->has('password_confirmation') ? 'password_confirmation_error' : null"
                />
                <x-form.input-label
                    for="password_confirmation"
                    :value="__('validation.attributes.password_confirmation')"
                />
                <x-form.input-error
                    id="password_confirmation_error"
                    :message="$errors->first('password_confirmation')"
                />
            </div>

            {{-- Terms --}}
            <div class="form-check">
                <input
                    type="checkbox"
                    id="terms"
                    name="terms"
                    @checked(old('terms'))
                    required
                    class="form-check-input {{ $errors->has('terms') ? 'is-invalid' : '' }}"
                />
                <label class="form-check-label" for="terms">
                    Ik ga akkoord met de
                    <a href="{{ route('terms.terms') }}" target="_blank">voorwaarden</a> en
                    <a href="{{ route('terms.privacy') }}" target="_blank">privacy&nbsp;beleid</a>
                </label>
                <x-form.input-error id="terms_error" :message="$errors->first('terms')" />
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <x-form.button :value="__('Register')" />
                <a href="{{ route('login') }}">@lang('Already registered?')</a>
            </div>
        </form>
    </x-authcard>
</x-layouts.app>

<x-layouts.app>
    <x-authcard :cardTitle="__('Reset Password')">
        <form method="POST" action="{{ route('password.store') }}" novalidate>
            <x-csrf-input />

            {{-- Password Reset Token --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}" />

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
                    placeholder="{{ __('validation.attributes.password') }}"
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

            {{-- Submit --}}
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <x-form.button :value="__('Reset Password')" />
                <a href="{{ route('login') }}">@lang('Log in')</a>
            </div>
        </form>
    </x-authcard>
</x-layouts.app>

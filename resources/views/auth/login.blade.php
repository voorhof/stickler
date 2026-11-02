<x-layouts.app>
    <x-authcard :cardTitle="__('Log in')">
        {{-- Session Status --}}
        <x-authcard.session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" novalidate>
            <x-csrf-input />

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
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    :describedBy="$errors->has('password') ? 'password_error' : null"
                />
                <x-form.input-label for="password" :value="__('validation.attributes.password')" />
                <x-form.input-error id="password_error" :message="$errors->first('password')" />
            </div>

            {{-- Remember Me --}}
            <div class="form-check">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    @checked(old('remember'))
                    required
                    class="form-check-input {{ $errors->has('remember') ? 'is-invalid' : '' }}"
                />
                <label class="form-check-label" for="remember"> {{ __('Remember me') }} </label>
                <x-form.input-error id="remember_error" :message="$errors->first('remember')" />
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <x-form.button :value="__('Log in')" />

                <a href="{{ route('register') }}">@lang('Register')</a>
            </div>

            @if (Route::has('password.request'))
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-3">
                    <a href="{{ route('password.request') }}">@lang('Forgot your password?')</a>
                </div>
            @endif
        </form>
    </x-authcard>
</x-layouts.app>

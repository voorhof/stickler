<x-layouts.app>
    <x-authcard :cardTitle="__('Forgot your password?')">
        <p>
            @lang('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.')
        </p>

        {{-- Session Status --}}
        <x-authcard.session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" novalidate>
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
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    :describedBy="$errors->has('email') ? 'email_error' : null"
                />
                <x-form.input-label for="email" :value="__('validation.attributes.email')" />
                <x-form.input-error id="email_error" :message="$errors->first('email')" />
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <x-form.button :value="__('Email password reset link')" />
                <a href="{{ route('login') }}">@lang('Log in')</a>
            </div>
        </form>
    </x-authcard>
</x-layouts.app>

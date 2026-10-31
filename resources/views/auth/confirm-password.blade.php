<x-layouts.app>
    <x-authcard :cardTitle="__('Confirm Password')">
        <p class="text-success-emphasis">
            @lang('This is a secure area of the application. Please confirm your password before continuing.')
        </p>

        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            <x-csrf-input />

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

            {{-- Submit --}}
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <x-form.button :value="__('Submit')" />
            </div>
        </form>
    </x-authcard>
</x-layouts.app>

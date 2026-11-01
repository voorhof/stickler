<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>{{ __('Edit profile') }}</h1>
                        <a href="{{ route('account.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            {{ __('Back to dashboard') }}
                        </a>
                    </div>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success" role="alert">
                            {{ __('Your profile has been updated successfully.') }}
                        </div>
                    @endif

                    <form action="{{ route('account.profile.update') }}" method="POST">
                        <x-csrf-input />

                        {{-- Personal Information --}}
                        <h2 class="mb-3">{{ __('Personal Information') }}</h2>

                        <div class="form-floating mb-3">
                            <input
                                type="text"
                                @class(['form-control', 'is-invalid' => $errors->has('name')])
                                id="nameInput"
                                name="name"
                                value="{{ old('name', auth()->user()->name) }}"
                                placeholder="{{ __('Name') }}"
                                autocomplete="name"
                                required
                                maxlength="255"
                            />
                            <label for="nameInput">
                                {{ __('Name') }}
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
                                value="{{ old('email', auth()->user()->email) }}"
                                placeholder="{{ __('Email address') }}"
                                autocomplete="email"
                                required
                                maxlength="255"
                            />
                            <label for="emailInput">
                                {{ __('Email address') }}
                                <span class="opacity-50">*</span>
                            </label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <x-form.button :value="__('Save profile')" class="mb-4" />

                        {{-- Change Password --}}
                        <hr class="my-4" />
                        <h2 class="mb-3">{{ __('Change password') }}</h2>

                        <div class="form-floating mb-3">
                            <input
                                type="password"
                                @class(['form-control', 'is-invalid' => $errors->has('current_password')])
                                id="currentPasswordInput"
                                name="current_password"
                                placeholder="{{ __('Current password') }}"
                                autocomplete="new-password"
                            />
                            <label for="currentPasswordInput"> {{ __('Current password') }} </label>
                            <small class="form-text text-muted">{{ __('Required when changing your password.') }}</small>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input
                                type="password"
                                @class(['form-control', 'is-invalid' => $errors->has('password')])
                                id="passwordInput"
                                name="password"
                                placeholder="{{ __('New password') }}"
                                autocomplete="new-password"
                            />
                            <label for="passwordInput"> {{ __('New password') }} </label>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input
                                type="password"
                                @class(['form-control', 'is-invalid' => $errors->has('password_confirmation')])
                                id="passwordConfirmationInput"
                                name="password_confirmation"
                                placeholder="{{ __('Confirm new password') }}"
                                autocomplete="new-password"
                            />
                            <label for="passwordConfirmationInput"> {{ __('Confirm new password') }} </label>
                        </div>

                        <x-form.button :value="__('Update password')" />
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

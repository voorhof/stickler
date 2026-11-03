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

                    {{-- Profile fields --}}
                    <form action="{{ route('account.profile.update') }}" method="POST">
                        <x-csrf-input />

                        {{-- Personal Information --}}
                        <h2 class="mb-3">{{ __('Personal Information') }}</h2>

                        {{-- Name --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                id="nameInput"
                                name="name"
                                :value="old('name', auth()->user()->name)"
                                placeholder="{{ __('validation.attributes.name') }}"
                                required
                                autocomplete="name"
                                maxlength="255"
                                class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('name') ? 'name_error' : null"
                            />
                            <x-form.input-label for="nameInput" :value="__('validation.attributes.name')" />
                            <x-form.input-error id="name_error" :message="$errors->first('name')" />
                        </div>

                        {{-- Email Address --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                type="email"
                                id="emailInput"
                                name="email"
                                :value="old('email', auth()->user()->email)"
                                placeholder="{{ __('validation.attributes.email') }}"
                                required
                                maxlength="255"
                                autocomplete="email"
                                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('email') ? 'email_error' : null"
                            />
                            <x-form.input-label for="emailInput" :value="__('validation.attributes.email')" />
                            <x-form.input-error id="email_error" :message="$errors->first('email')" />
                        </div>

                        <x-form.button :value="__('Save profile')" class="mb-4" />

                        {{-- Change Password --}}
                        <hr class="my-4" />
                        <h2 class="mb-3">{{ __('Change password') }}</h2>

                        {{-- Current Password --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                type="password"
                                id="currentPasswordInput"
                                name="current_password"
                                placeholder="{{ __('Current password') }}"
                                required
                                autocomplete="new-password"
                                class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('current_password') ? 'password_error' : null"
                            />
                            <x-form.input-label for="currentPasswordInput" :value="__('Current password')" />
                            <small class="form-text text-muted">{{ __('Required when changing your password.') }}</small>
                            <x-form.input-error id="password_error" :message="$errors->first('current_password')" />
                        </div>

                        {{-- New Password --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                type="password"
                                id="passwordInput"
                                name="password"
                                placeholder="{{ __('New password') }}"
                                required
                                autocomplete="new-password"
                                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('password') ? 'password_error' : null"
                            />
                            <x-form.input-label for="password" :value="__('New password')" />
                            <x-form.input-error id="password_error" :message="$errors->first('password')" />
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                type="password"
                                id="passwordConfirmationInput"
                                name="password_confirmation"
                                placeholder="{{ __('Confirm new password') }}"
                                required
                                autocomplete="new-password"
                                class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('password_confirmation') ? 'password_confirmation_error' : null"
                            />
                            <x-form.input-label for="passwordConfirmationInput" :value="__('Confirm new password')" />
                            <x-form.input-error
                                id="password_confirmation_error"
                                :message="$errors->first('password_confirmation')"
                            />
                        </div>

                        <x-form.button :value="__('Update password')" class="mb-4" />
                    </form>

                    <hr class="my-4" />

                    {{-- Delete user account --}}
                    <div>
                        <h2>
                            @lang('Delete Account')
                        </h2>

                        <p>
                            @lang('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.')
                        </p>

                        {{-- Modal trigger --}}
                        <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteAccountModal">
                            {{ __('Delete Account') }}
                        </button>

                        {{-- Modal --}}
                        @push('modals-bottom')
                            <div
                                class="modal fade"
                                id="deleteAccountModal"
                                tabindex="-1"
                                role="dialog"
                                aria-labelledby="deleteAccountModalLabel"
                                aria-hidden="true"
                            >
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="deleteAccountModalLabel">
                                                @lang('Are you sure you want to delete your account?')
                                            </h1>
                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-dismiss="modal"
                                                aria-label="Close"
                                            ></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>
                                                @lang('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.')
                                            </p>

                                            <form
                                                method="post"
                                                action="{{ route('account.profile.destroy') }}"
                                                id="deleteAccountForm"
                                            >
                                                <x-csrf-input />
                                                @method('delete')

                                                {{-- Hidden username --}}
                                                <input
                                                    type="email"
                                                    name="email"
                                                    autocomplete="username"
                                                    value="{{ auth()->user()->email }}"
                                                    class="visually-hidden"
                                                    tabindex="-1"
                                                    aria-hidden="true"
                                                    readonly
                                                />

                                                {{-- Password --}}
                                                <div class="form-floating mb-3">
                                                    <x-form.input-text
                                                        type="password"
                                                        id="password"
                                                        name="password"
                                                        placeholder="{{ __('validation.attributes.password') }}"
                                                        autocomplete="new-password"
                                                        required
                                                        class="{{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}"
                                                        :describedBy="$errors->userDeletion->has('password') ? 'password_error' : null"
                                                    />
                                                    <x-form.input-label
                                                        for="password"
                                                        :value="__('validation.attributes.password')"
                                                    />
                                                    <x-form.input-error
                                                        id="password_error"
                                                        :message="$errors->userDeletion->first('password')"
                                                    />
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                @lang(__('No'))
                                            </button>

                                            <x-form.button
                                                variant="danger"
                                                :value="__('Delete')"
                                                form="deleteAccountForm"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

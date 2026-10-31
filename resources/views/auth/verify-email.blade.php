<x-layouts.app>
    <x-authcard :cardTitle="__('Verify Email Address')">
        <p>
            @lang("Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.")
        </p>

        @if (session('status') === 'verification-link-sent')
            <div class="small text-success mb-4">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                <x-csrf-input />
                <x-form.button :value="__('Resend Verification Email')" />
            </form>

            <form method="POST" action="{{ route('logout') }}">
                <x-csrf-input />
                <x-form.button variant="outline-secondary" :value="__('Log out')" />
            </form>
        </div>
    </x-authcard>
</x-layouts.app>

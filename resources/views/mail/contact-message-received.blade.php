<x-mail::message>
# @lang('Hello!')

@lang('mail/contact-message-received.intro')

@lang('Sender'):
<strong>{{ $sender }}</strong>

@lang('Subject'):
<strong>{{ $subject }}</strong>

<x-mail::panel>
    {{ $messageText }}
</x-mail::panel>

<x-mail::button :url="$url">
    @lang('mail/contact-message-received.button')
</x-mail::button>
</x-mail::message>

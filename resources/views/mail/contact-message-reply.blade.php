<x-mail::message>
# @lang('Hello!')

@lang('mail/contact-message-reply.intro')


<x-mail::panel>
    {{ $replyText }}
</x-mail::panel>


@lang('Subject'):
<strong>{{ $subject }}</strong>


@lang('Your original message'):

{{ $messageText }}

<x-mail::button :url="$url">
    @lang('mail/contact-message-reply.button')
</x-mail::button>
</x-mail::message>

@lang('Hello!')

@lang('mail/contact-message-received.intro')

@lang('Sender'): {{ $sender }}

@lang('Subject'): {{ $subject }}

@lang('Message'):
{{ $messageText }}

@lang('mail/contact-message-received.button')
{{ $url }}

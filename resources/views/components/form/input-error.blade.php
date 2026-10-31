@props(['message'])

@if ($message)
    <div {{
        $attributes->merge([
            'class' => 'invalid-feedback',
            'role' => 'alert',
        ])
    }}>
        {{ $message }}
    </div>
@endif

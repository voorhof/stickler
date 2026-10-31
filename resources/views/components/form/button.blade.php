@props(['value', 'variant' => 'primary'])

<button {{
    $attributes->merge([
        'type' => 'submit',
        'class' => 'btn btn-'.$variant,
    ])
}}>
    {{ $value ?? $slot }}
</button>

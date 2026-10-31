@props(['disabled' => false, 'describedBy' => null])

<input
    @disabled($disabled)
    {{
        $attributes->merge([
            'type' => 'text',
            'class' => 'form-control',
            'aria-describedby' => $describedBy,
        ])
    }}
/>

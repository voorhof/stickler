<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $billingSameAsShipping = $this->boolean('billing_same_as_shipping');

        $billingRules = $billingSameAsShipping
            ? []
            : [
                'billing_street' => ['bail', 'required', 'string', 'max:255'],
                'billing_city' => ['bail', 'required', 'string', 'max:255'],
                'billing_postal_code' => ['bail', 'required', 'string', 'max:255'],
                'billing_country' => ['bail', 'required', 'string', 'max:255'],
            ];

        return array_merge([
            'name' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email', 'max:255'],
            'phone' => ['bail', 'nullable', 'string', 'max:255'],
            'shipping_street' => ['bail', 'required', 'string', 'max:255'],
            'shipping_city' => ['bail', 'required', 'string', 'max:255'],
            'shipping_postal_code' => ['bail', 'required', 'string', 'max:255'],
            'shipping_country' => ['bail', 'required', 'string', 'max:255'],
            'billing_same_as_shipping' => ['bail', 'boolean'],
            'notes' => ['bail', 'nullable', 'string', 'max:2550'],
        ], $billingRules);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Name'),
            'email' => __('Email address'),
            'phone' => __('Phone'),
            'shipping_street' => __('Street'),
            'shipping_city' => __('City'),
            'shipping_postal_code' => __('Postal code'),
            'shipping_country' => __('Country'),
            'billing_street' => __('Street'),
            'billing_city' => __('City'),
            'billing_postal_code' => __('Postal code'),
            'billing_country' => __('Country'),
            'notes' => __('Notes'),
        ];
    }
}

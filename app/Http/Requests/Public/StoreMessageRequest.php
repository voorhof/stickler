<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
        return [
            'name' => ['bail', 'present', 'required', 'string', 'max:255'],
            'email' => ['bail', 'present', 'required', 'email', 'max:255'],
            'phone' => ['bail', 'present', 'nullable', 'string', 'max:255'],
            'subject' => ['bail', 'present', 'required', 'string', 'max:255'],
            'message' => ['bail', 'present', 'required', 'string', 'max:2550'],
        ];
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
            'subject' => __('Subject'),
            'message' => __('Message'),
        ];
    }
}

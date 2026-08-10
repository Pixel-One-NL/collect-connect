<?php

declare(strict_types=1);

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<\Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        $createAccount = $this->boolean('create_account');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::when($createAccount, ['unique:users,email']),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'shipping_method_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string', 'max:64'],
            'create_account' => ['sometimes', 'boolean'],
            'password' => [
                Rule::requiredIf($createAccount),
                'nullable',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }
}

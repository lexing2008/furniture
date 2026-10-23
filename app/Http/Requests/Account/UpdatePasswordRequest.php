<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Changing of the password of the logged in user from the account.
 */
class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Заполните поле «:attribute».',
            'confirmed' => 'Пароли не совпадают.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'password.min' => 'Пароль должен быть не короче :min символов.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'текущий пароль',
            'password' => 'новый пароль',
        ];
    }
}

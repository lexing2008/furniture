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
            'required' => __('Заполните поле «:attribute».'),
            'confirmed' => __('Пароли не совпадают.'),
            'current_password.current_password' => __('Текущий пароль указан неверно.'),
            'password.min' => __('Пароль должен быть не короче :min символов.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => __('текущий пароль'),
            'password' => __('новый пароль'),
        ];
    }
}

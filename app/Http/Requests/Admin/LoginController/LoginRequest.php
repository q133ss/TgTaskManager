<?php

namespace App\Http\Requests\Admin\LoginController;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    $user = User::where('first_name', $this->name)->first();
                    $password = 'password';

                    if (!$user || $value != $password) {
                        $fail('Неверный логин или пароль');
                    }
                    if($user->is_admin === false) {
                        $fail('У вас нет прав доступа');
                    }
                }
            ],
        ];
    }
}

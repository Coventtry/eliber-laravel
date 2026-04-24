<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'                => 'required|string|max:255',
            'email'                 => 'required|email|max:255|unique:users,email',
            'usuario'               => 'required|string|max:255|unique:users,usuario',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'rol'                   => 'required|in:admin,bibliotecario',
        ];
    }
}

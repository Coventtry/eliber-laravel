<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'nombre' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$userId}",
            'usuario' => "required|string|max:255|unique:users,usuario,{$userId}",
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable',
            'socio_id' => 'nullable|exists:socios,id',
        ];
    }
}

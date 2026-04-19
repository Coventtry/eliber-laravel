<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $socioId = $this->route('socio')?->id;

        return [
            'nombre'    => 'required|string|max:255',
            'apellido'  => 'required|string|max:255',
            'email'     => 'nullable|email|max:255|unique:socios,email' . ($socioId ? ",{$socioId}" : ''),
            'telefono'  => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'anio'      => 'nullable|integer|min:1|max:6',
            'division'  => 'nullable|integer|min:1|max:6',
        ];
    }
}

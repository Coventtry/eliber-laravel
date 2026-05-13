<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'autor' => 'nullable|string|max:255',
            'anio_publicacion' => 'nullable|integer|min:1000|max:'.date('Y'),
            'area_id' => 'required|exists:areas,id',
            'categoria' => 'nullable|string|max:255',
            'disponibilidad' => 'required|integer|min:0',
            'editorial' => 'nullable|string|max:255',
            'tipo_prestamo' => 'nullable|in:Solo consulta,Copia única,Transitorio',
            'pasillo' => 'nullable|string|max:10',
            'tipo_almacenamiento' => 'nullable|in:E,M',
            'estante' => 'nullable|integer|min:1|max:30',
            'nivel' => 'nullable|integer|min:1|max:6',
        ];
    }

    public function messages(): array
    {
        return [
            'disponibilidad.required' => 'Cantidad de ejemplares físicos disponibles en la biblioteca.',
            'disponibilidad.integer' => 'La disponibilidad debe ser un número entero.',
            'disponibilidad.min' => 'No puede haber cantidad negativa de ejemplares.',
        ];
    }
}

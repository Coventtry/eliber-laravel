<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestamoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'socio_id'         => 'required|exists:socios,id',
            'material_id'      => 'required|exists:materiales,id',
            'cantidad'         => 'required|integer|min:1',
            'fecha_devolucion' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(14)->toDateString(),
        ];
    }
}

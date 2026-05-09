<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrestamoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha_prestamo' => $this->fecha_prestamo?->format('Y-m-d'),
            'fecha_devolucion' => $this->fecha_devolucion?->format('Y-m-d'),
            'estado' => $this->estado,
            'cantidad' => $this->cantidad,
            'socio' => new SocioResource($this->whenLoaded('socio')),
            'material' => new MaterialResource($this->whenLoaded('material')),
        ];
    }
}

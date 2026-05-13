<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'material_id' => $this->material_id,
            'socio_id' => $this->socio_id,
            'estado' => $this->estado,
            'fecha_reserva' => $this->fecha_reserva?->format('Y-m-d'),
            'fecha_vencimiento' => $this->fecha_vencimiento?->format('Y-m-d'),
            'material' => new MaterialResource($this->whenLoaded('material')),
            'socio' => new SocioResource($this->whenLoaded('socio')),
        ];
    }
}

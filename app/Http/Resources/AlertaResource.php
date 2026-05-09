<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            'fecha_alerta' => $this->fecha_alerta?->format('Y-m-d H:i:s'),
            'leida' => $this->leida,
            'prestamo_id' => $this->prestamo_id,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'autor' => $this->autor,
            'anio_publicacion' => $this->anio_publicacion,
            'categoria' => $this->categoria,
            'codigo' => $this->codigo,
            'disponibilidad' => $this->disponibilidad,
            'editorial' => $this->editorial,
            'clasificacion_fisica' => $this->clasificacion_fisica,
            'tipo_prestamo' => $this->tipo_prestamo,
            'area' => new AreaResource($this->whenLoaded('area')),
        ];
    }
}

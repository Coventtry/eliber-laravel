<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'usuario' => $this->usuario,
            'activo' => $this->activo,
            'rol' => $this->roles->first()?->name,
            'picture_url' => $this->picture_url,
            'socio_id' => $this->socio_id,
        ];
    }
}

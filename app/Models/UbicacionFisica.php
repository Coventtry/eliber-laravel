<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UbicacionFisica extends Model
{
    protected $table = 'ubicaciones_fisicas';
    public $timestamps = false;

    protected $fillable = [
        'material_id', 'pasillo', 'estante', 'nivel',
        'codigo_ubicacion', 'fecha_asignacion',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}

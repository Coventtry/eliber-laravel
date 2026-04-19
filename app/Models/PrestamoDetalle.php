<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestamoDetalle extends Model
{
    protected $table = 'prestamos_detalle';
    public $timestamps = false;

    protected $fillable = ['prestamo_id', 'libro_id', 'cantidad_prestada', 'cantidad_devuelta'];

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }
}

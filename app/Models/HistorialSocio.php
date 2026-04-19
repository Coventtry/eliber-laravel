<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialSocio extends Model
{
    protected $table = 'historial_socios';
    public $timestamps = false;

    protected $fillable = ['id_socio', 'accion', 'fecha', 'observaciones'];

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'id_socio');
    }
}

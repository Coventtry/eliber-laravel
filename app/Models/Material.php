<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'materiales';
    public $timestamps = false;

    protected $fillable = [
        'titulo', 'autor', 'anio_publicacion', 'area_id',
        'categoria', 'codigo', 'disponibilidad', 'editorial',
        'clasificacion_fisica',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function ubicacion(): HasOne
    {
        return $this->hasOne(UbicacionFisica::class);
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    public function scopeDisponible($query)
    {
        return $query->where('disponibilidad', '>', 0);
    }

    public function scopePorArea($query, int $areaId)
    {
        return $query->where('area_id', $areaId);
    }
}

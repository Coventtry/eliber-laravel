<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materiales';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = [
        'titulo', 'autor', 'anio_publicacion', 'area_id',
        'categoria', 'codigo', 'disponibilidad', 'disponibilidad_reservada',
        'editorial', 'clasificacion_fisica', 'institucion_id',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
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

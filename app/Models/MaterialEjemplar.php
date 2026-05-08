<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaterialEjemplar extends Model
{
    protected $table = 'material_ejemplares';

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'material_id',
        'institucion_id',
        'codigo_ejemplar',
        'estado',
        'notas',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class, 'ejemplar_id');
    }

    public function prestamoActivo(): HasOne
    {
        return $this->hasOne(Prestamo::class, 'ejemplar_id')
            ->whereIn('estado', ['activo', 'pendiente', 'atrasado']);
    }

    public function scopeDisponible($query)
    {
        return $query->where('estado', 'disponible');
    }

    public function scopePrestado($query)
    {
        return $query->where('estado', 'prestado');
    }

    public function scopeReservado($query)
    {
        return $query->where('estado', 'reservado');
    }
}

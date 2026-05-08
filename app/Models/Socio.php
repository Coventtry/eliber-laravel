<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Socio extends Model
{
    protected $table = 'socios';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'nombre', 'apellido', 'telefono', 'direccion',
        'email', 'anio', 'division', 'activo', 'institucion_id',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialSocio::class, 'id_socio');
    }

    public function prestamosActivos(): HasMany
    {
        return $this->prestamos()->whereIn('estado', ['activo', 'pendiente', 'atrasado']);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    public function scopeBuscarEmail($query, string $prefijo)
    {
        return $query->where('email', 'like', $prefijo.'%');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }
}

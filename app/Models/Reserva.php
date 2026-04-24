<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'material_id',
        'socio_id',
        'estado',
        'fecha_reserva',
        'fecha_vencimiento',
        'institucion_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected function casts(): array
    {
        return [
            'fecha_reserva' => 'datetime',
            'fecha_vencimiento' => 'datetime',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeActiva($query)
    {
        return $query->whereIn('estado', ['pendiente', 'aprobada']);
    }
}
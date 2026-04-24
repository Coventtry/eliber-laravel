<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'institucion_id', 'prestamo_id', 'tipo', 'descripcion', 'fecha_alerta', 'leida',
    ];

    protected function casts(): array
    {
        return [
            'fecha_alerta' => 'datetime',
            'leida'        => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }
}

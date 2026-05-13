<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Multa extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'multas';

    protected $fillable = [
        'socio_id', 'prestamo_id', 'monto', 'motivo', 'observaciones',
        'pagada', 'fecha_pago', 'fecha_creacion', 'institucion_id',
    ];

    protected function casts(): array
    {
        return [
            'pagada'        => 'boolean',
            'monto'         => 'decimal:2',
            'fecha_pago'    => 'date',
            'fecha_creacion' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('pagada', false);
    }

    public function scopePagadas($query)
    {
        return $query->where('pagada', true);
    }
}

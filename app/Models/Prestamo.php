<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestamo extends Model
{
    protected $table = 'prestamos';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = [
        'socio_id', 'material_id', 'fecha_prestamo',
        'fecha_devolucion', 'estado', 'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'fecha_prestamo'   => 'date',
            'fecha_devolucion' => 'date',
        ];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PrestamoDetalle::class);
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeAtrasado($query)
    {
        return $query->where('estado', 'atrasado');
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeDevuelto($query)
    {
        return $query->where('estado', 'devuelto');
    }

    public function scopeVencimientoProximo($query, int $dias = 4)
    {
        return $query->whereIn('estado', ['activo', 'pendiente'])
            ->whereBetween('fecha_devolucion', [now()->toDateString(), now()->addDays($dias)->toDateString()]);
    }

    public function getLinkWhatsappAttribute(): ?string
    {
        if (!$this->socio) return null;
        $telefono = preg_replace('/\D/', '', $this->socio->telefono ?? '');
        if (empty($telefono)) return null;
        $msg = urlencode("Hola {$this->socio->nombre}, recordamos que el préstamo de *{$this->material->titulo}* vence el {$this->fecha_devolucion->format('d/m/Y')}.");
        return "https://wa.me/54{$telefono}?text={$msg}";
    }
}

<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = ['institucion_id', 'clave', 'valor'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public static function get(int $institucionId, string $clave, mixed $default = null): mixed
    {
        $valor = once(fn () => static::where('institucion_id', $institucionId)
            ->where('clave', $clave)
            ->value('valor'));

        return $valor ?? $default;
    }

    public static function set(int $institucionId, string $clave, mixed $valor): void
    {
        static::updateOrCreate(
            ['institucion_id' => $institucionId, 'clave' => $clave],
            ['valor' => $valor]
        );
    }
}

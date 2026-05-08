<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = ['institucion_id', 'clave', 'valor'];

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public static function get(int $institucionId, string $clave, mixed $default = null): mixed
    {
        $row = static::where('institucion_id', $institucionId)->where('clave', $clave)->first();

        return $row ? $row->valor : $default;
    }

    public static function set(int $institucionId, string $clave, mixed $valor): void
    {
        static::updateOrCreate(
            ['institucion_id' => $institucionId, 'clave' => $clave],
            ['valor' => $valor]
        );
    }
}

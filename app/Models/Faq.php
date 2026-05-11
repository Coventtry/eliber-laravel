<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $fillable = ['institucion_id', 'pregunta', 'respuesta', 'orden', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }
}

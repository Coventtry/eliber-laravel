<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackCard extends Model
{
    protected $fillable = [
        'institucion_id', 'creado_por', 'titulo', 'descripcion',
        'tags', 'prioridad', 'columna',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }
}

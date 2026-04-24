<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    protected $fillable = ['institucion_id', 'label', 'url', 'orden'];

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }
}

<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    use HasFactory;

    protected $fillable = ['institucion_id', 'label', 'url', 'orden'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }
}

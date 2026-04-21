<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use SoftDeletes;

    protected $table = 'noticias';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = ['titulo', 'descripcion', 'imagen', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) return null;
        return \Storage::disk('public')->exists('noticias/' . $this->imagen)
            ? \Storage::disk('public')->url('noticias/' . $this->imagen)
            : null;
    }
}

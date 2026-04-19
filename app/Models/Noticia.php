<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    protected $table = 'noticias';
    public $timestamps = false;

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

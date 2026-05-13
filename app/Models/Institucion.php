<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institucion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'instituciones';

    protected $fillable = [
        'nombre', 'slug', 'estado',
        'anuncio_texto', 'anuncio_estilo', 'anuncio_activo',
    ];

    protected function casts(): array
    {
        return [
            'anuncio_activo' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function socios(): HasMany
    {
        return $this->hasMany(Socio::class);
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class);
    }

    public function anotaciones(): HasMany
    {
        return $this->hasMany(Anotacion::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class);
    }

    public function configuraciones(): HasMany
    {
        return $this->hasMany(Configuracion::class);
    }
}

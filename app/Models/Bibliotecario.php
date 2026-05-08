<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Bibliotecario extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table = 'bibliotecarios';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'usuario',
        'password',
        'picture',
        'institucion_id',
        'socio_id',
    ];

    protected $hidden = ['password', 'remember_token', 'Clave_unica'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function getPictureUrlAttribute(): ?string
    {
        if ($this->picture && \Storage::disk('public')->exists('uploads/'.$this->picture)) {
            return asset('storage/uploads/'.$this->picture);
        }

        return null;
    }
}

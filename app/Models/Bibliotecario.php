<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Bibliotecario extends Authenticatable
{
    use Notifiable, HasRoles;

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
    ];

    protected $hidden = ['password', 'remember_token', 'Clave_unica'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function getPictureUrlAttribute(): ?string
    {
        if ($this->picture && \Storage::disk('public')->exists('uploads/' . $this->picture)) {
            return \Storage::disk('public')->url('uploads/' . $this->picture);
        }
        return null;
    }
}

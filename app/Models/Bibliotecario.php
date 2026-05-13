<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @deprecated The auth provider is User (not this model).
 *             Kept for legacy migration references. Do NOT use for new code.
 */
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
        if ($this->picture && \Storage::disk('public')->exists('uploads/' . $this->picture)) {
            return asset('storage/uploads/' . $this->picture);
        }
        return null;
    }
}

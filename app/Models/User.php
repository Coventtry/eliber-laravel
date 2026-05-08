<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'nombre',
        'apellido',
        'email',
        'usuario',
        'password',
        'picture',
        'wallpaper',
        'telefono',
        'anio',
        'division',
        'activo',
        'institucion_id',
        'socio_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function getPictureUrlAttribute(): ?string
    {
        $archivo = trim((string) $this->picture);
        if ($archivo && Storage::disk('public')->exists('uploads/'.$archivo)) {
            return asset('storage/uploads/'.$archivo);
        }

        return null;
    }

    public function getWallpaperUrlAttribute(): ?string
    {
        $archivo = trim((string) $this->wallpaper);
        if ($archivo && Storage::disk('public')->exists('wallpapers/'.$archivo)) {
            return asset('storage/wallpapers/'.$archivo);
        }

        return null;
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }
}

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
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'nombre',
        'email',
        'usuario',
        'password',
        'picture',
        'wallpaper',
        'telefono',
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
        $file = trim((string) $this->picture);
        if ($file && Storage::disk('public')->exists('uploads/' . $file)) {
            return asset('storage/uploads/' . $file);
        }
        return null;
    }

    public function getWallpaperUrlAttribute(): ?string
    {
        $file = trim((string) $this->wallpaper);
        if ($file && Storage::disk('public')->exists('wallpapers/' . $file)) {
            return asset('storage/wallpapers/' . $file);
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
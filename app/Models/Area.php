<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $table = 'areas';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = ['codigo_dewey', 'nombre', 'Abreviado'];

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}

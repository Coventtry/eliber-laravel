<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class CategoriaFisica extends Model
{
    public $timestamps = false;

    protected $table = 'categorias_fisicas';

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = ['nombre', 'institucion_id'];
}

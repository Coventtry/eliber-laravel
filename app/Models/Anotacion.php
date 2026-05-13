<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anotacion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'anotaciones';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = ['anotacion', 'fecha', 'institucion_id'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }
}

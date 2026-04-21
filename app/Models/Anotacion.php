<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anotacion extends Model
{
    use SoftDeletes;

    protected $table = 'anotaciones';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = ['anotacion', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }
}

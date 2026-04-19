<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anotacion extends Model
{
    protected $table = 'anotaciones';
    public $timestamps = false;

    protected $fillable = ['anotacion', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }
}

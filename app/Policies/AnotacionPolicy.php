<?php

namespace App\Policies;

use App\Models\Anotacion;
use App\Models\User;

class AnotacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-anotaciones');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-anotaciones');
    }

    public function delete(User $user, Anotacion $anotacion): bool
    {
        return $user->can('gestionar-anotaciones') && $user->institucion_id === $anotacion->institucion_id;
    }
}

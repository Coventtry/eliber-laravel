<?php

namespace App\Policies;

use App\Models\Prestamo;
use App\Models\User;

class PrestamoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }

    public function view(User $user, Prestamo $prestamo): bool
    {
        return $user->can('gestionar-prestamos') && $user->institucion_id === $prestamo->institucion_id;
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }

    public function update(User $user, Prestamo $prestamo): bool
    {
        return $user->can('gestionar-prestamos') && $user->institucion_id === $prestamo->institucion_id;
    }
}

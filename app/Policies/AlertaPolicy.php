<?php

namespace App\Policies;

use App\Models\User;

class AlertaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }

    public function update(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }

    public function baja(User $user): bool
    {
        return $user->can('gestionar-prestamos') && $user->can('gestionar-materiales');
    }
}

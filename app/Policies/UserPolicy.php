<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-usuarios');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-usuarios');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('gestionar-usuarios')
            && $user->institucion_id === $target->institucion_id;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->can('gestionar-usuarios')
            && $user->institucion_id === $target->institucion_id
            && $user->id !== $target->id;
    }
}

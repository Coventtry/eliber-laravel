<?php

namespace App\Policies;

use App\Models\User;

class ReservaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }

    public function delete(User $user): bool
    {
        return $user->can('gestionar-prestamos');
    }
}

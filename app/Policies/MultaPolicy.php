<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Multa;

class MultaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-multas');
    }

    public function view(User $user, Multa $multa): bool
    {
        return $user->can('gestionar-multas') && $user->institucion_id === $multa->institucion_id;
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-multas');
    }

    public function pay(User $user, Multa $multa): bool
    {
        return $user->can('gestionar-multas') && $user->institucion_id === $multa->institucion_id;
    }

    public function forgive(User $user, Multa $multa): bool
    {
        return $user->can('gestionar-multas') && $user->institucion_id === $multa->institucion_id;
    }
}

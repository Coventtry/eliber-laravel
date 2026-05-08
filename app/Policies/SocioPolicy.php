<?php

namespace App\Policies;

use App\Models\Socio;
use App\Models\User;

class SocioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-socios');
    }

    public function view(User $user, Socio $socio): bool
    {
        return $user->can('gestionar-socios') && $this->isSameInstitution($user, $socio);
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-socios');
    }

    public function update(User $user, Socio $socio): bool
    {
        return $user->can('gestionar-socios') && $this->isSameInstitution($user, $socio);
    }

    public function delete(User $user, Socio $socio): bool
    {
        return $user->can('gestionar-socios') && $this->isSameInstitution($user, $socio);
    }

    private function isSameInstitution(User $user, Socio $socio): bool
    {
        return $user->institucion_id === $socio->institucion_id;
    }
}

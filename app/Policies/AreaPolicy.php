<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;

class AreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-areas');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-areas');
    }

    public function update(User $user, Area $area): bool
    {
        return $user->can('gestionar-areas') && $user->institucion_id === $area->institucion_id;
    }

    public function delete(User $user, Area $area): bool
    {
        return $user->can('gestionar-areas') && $user->institucion_id === $area->institucion_id;
    }
}

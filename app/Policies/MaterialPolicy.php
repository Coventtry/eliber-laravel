<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Material;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-materiales');
    }

    public function view(User $user, Material $material): bool
    {
        return $user->can('gestionar-materiales') && $user->institucion_id === $material->institucion_id;
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-materiales');
    }

    public function update(User $user, Material $material): bool
    {
        return $user->can('gestionar-materiales') && $user->institucion_id === $material->institucion_id;
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->can('gestionar-materiales') && $user->institucion_id === $material->institucion_id;
    }
}
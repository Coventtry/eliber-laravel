<?php

namespace App\Policies;

use App\Models\Noticia;
use App\Models\User;

class NoticiaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar-noticias');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar-noticias');
    }

    public function update(User $user, Noticia $noticia): bool
    {
        return $user->can('gestionar-noticias') && $user->institucion_id === $noticia->institucion_id;
    }

    public function delete(User $user, Noticia $noticia): bool
    {
        return $user->can('gestionar-noticias') && $user->institucion_id === $noticia->institucion_id;
    }
}

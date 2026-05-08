<?php

if (! function_exists('tenantId')) {
    /**
     * Devuelve el institucion_id activo para el usuario autenticado.
     * Si es admin y cambió de institución en sesión, usa el valor de sesión.
     */
    function tenantId(): ?int
    {
        if (! auth()->check()) {
            return null;
        }
        $user = auth()->user();
        if ($user->hasRole('admin') && session()->has('admin_institucion_id')) {
            return (int) session('admin_institucion_id');
        }

        return $user->institucion_id;
    }
}

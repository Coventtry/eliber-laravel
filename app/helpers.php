<?php

if (!function_exists('tenantId')) {
    function tenantId(): ?int
    {
        if (auth()->user()?->hasRole('admin') && session()->has('admin_institucion_id')) {
            return (int) session('admin_institucion_id');
        }

        return auth()->user()?->institucion_id;
    }
}

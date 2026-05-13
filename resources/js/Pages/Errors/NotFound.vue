<template>
  <div class="container text-center py-5">
    <h1 class="display-1 text-muted">404</h1>
    <p class="lead">Página no encontrada</p>
    <p class="text-muted">La página que buscás no existe o fue movida.</p>
    <Link href="/" class="btn btn-primary mt-3">Volver al inicio</Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

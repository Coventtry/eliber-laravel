<template>
    <Head title="Acerca del Proyecto" />
    <PublicNavbar />

    <div class="hero-section text-center">
        <div class="container">
            <img src="/img/logo.png" alt="E-liber" height="100" class="mb-3 animate-pulse-subtle">
            <h1 class="display-4 font-weight-bold">Acerca del Proyecto</h1>
            <p class="lead">Sistema de Gestión Bibliotecaria</p>
        </div>
    </div>

    <div class="container page-content py-5">
            <div class="card shadow-sm p-4 rounded-lg">
            <h2 class="mb-3 text-eliber">e-Liber - Sistema de Gestión Bibliotecaria</h2>
            <p class="lead">
                e-Liber es un sistema de gestión bibliotecaria diseñado para facilitar la administración de libros, socios y préstamos.
                Ideal para pequeñas bibliotecas escolares.
            </p>

            <h4 class="mt-4 text-eliber">Características del Sistema</h4>
            <ul class="mt-3">
                <li><strong>Gestión de Materiales:</strong> Agregar, editar y buscar libros</li>
                <li><strong>Gestión de Socios:</strong> Registro, edición y visualización de usuarios</li>
                <li><strong>Gestión de Préstamos:</strong> Registrar préstamos y devoluciones con actualización automática de disponibilidad</li>
                <li><strong>Gestión de Áreas:</strong> Clasificación Dewey</li>
                <li><strong>Noticias:</strong> Panel de novedades para usuarios</li>
                <li><strong>Anotaciones:</strong> Sistema de notas internas</li>
            </ul>

            <h4 class="mt-4 text-eliber">Tecnologías Utilizadas</h4>
            <div class="d-flex flex-wrap gap-3 mt-3">
                <span class="badge badge-tech" style="background:#e34f26;">HTML5</span>
                <span class="badge badge-tech" style="background:#264de4;">CSS3</span>
                <span class="badge badge-tech" style="background:#7952b3;">Bootstrap 5</span>
                <span class="badge badge-tech" style="background:#474a8a;">PHP 8</span>
                <span class="badge badge-tech" style="background:#42b883;">Vue 3</span>
                <span class="badge badge-tech" style="background:#ff2d20;">Laravel 11</span>
            </div>

            <h4 class="mt-4 text-eliber">Video de Presentación</h4>
            <p>
                <a href="https://github.com/Coventtry/eliber-laravel" target="_blank" class="btn btn-outline-success">
                    <i class="bi bi-github me-2"></i>Ver en GitHub
                </a>
            </p>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import PublicNavbar from '@/Components/PublicNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
</script>
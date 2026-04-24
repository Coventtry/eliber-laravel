<template>
    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <Link :href="route('alumno.dashboard')" class="navbar-brand">
                <img src="/img/logo.png" alt="E-liber" height="36">
            </Link>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navAlumno">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navAlumno">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <Link :href="route('alumno.catalogo')" class="nav-link">
                            <i class="bi bi-book mr-1"></i>Catálogo
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link :href="route('alumno.reservas')" class="nav-link">
                            <i class="bi bi-bookmark mr-1"></i>Mis reservas
                        </Link>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" href="#">
                            <template v-if="auth.user.picture_url">
                                <img :src="auth.user.picture_url" alt="perfil" class="perfil-img mr-2">
                            </template>
                            <template v-else>
                                <i class="bi bi-person-circle" style="font-size:1.5rem; color:var(--eliber-accent);"></i>
                            </template>
                            <span class="ms-2">{{ auth.user.nombre }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="border-radius:10px; border:none; box-shadow:0 8px 25px rgba(0,0,0,.15);">
                            <h6 class="dropdown-header" style="color:var(--eliber-primary); font-weight:600;">{{ auth.user.usuario }}</h6>
                            <div class="dropdown-divider"></div>
                            <Link :href="route('perfil.edit')" class="dropdown-item">
                                <i class="bi bi-person-gear mr-1"></i>Mi perfil
                            </Link>
                            <div class="dropdown-divider"></div>
                            <form @submit.prevent="logout" method="POST">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right mr-1"></i>Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { usePage, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const auth = computed(() => page.props.auth)

function logout() {
    router.post(route('logout'))
}
</script>

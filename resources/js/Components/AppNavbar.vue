<template>
    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <Link :href="route('dashboard')" class="navbar-brand">
                <img src="/img/logo.png" alt="E-liber" height="36">
            </Link>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mr-auto">
                    <!-- Socios -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Socios</a>
                        <div class="dropdown-menu">
                            <Link :href="route('socios.create')" class="dropdown-item">Nuevo socio</Link>
                            <Link :href="route('socios.index')" class="dropdown-item">Buscar / Modificar</Link>
                        </div>
                    </li>

                    <!-- Materiales -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Materiales</a>
                        <div class="dropdown-menu">
                            <Link :href="route('materiales.create')" class="dropdown-item">Nuevo material</Link>
                            <Link :href="route('materiales.index')" class="dropdown-item">Buscar / Modificar</Link>
                            <Link :href="route('materiales.index')" class="dropdown-item">
                                <i class="bi bi-qr-code me-2"></i>Imprimir Código
                            </Link>
                        </div>
                    </li>

                    <!-- Áreas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Áreas</a>
                        <div class="dropdown-menu">
                            <Link :href="route('areas.create')" class="dropdown-item">Nueva área</Link>
                            <Link :href="route('areas.index')" class="dropdown-item">Modificar / Eliminar</Link>
                        </div>
                    </li>

                    <!-- Préstamos -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Préstamos</a>
                        <div class="dropdown-menu">
                            <Link :href="route('prestamos.create')" class="dropdown-item">Nuevo préstamo</Link>
                            <Link :href="route('prestamos.index')" class="dropdown-item">Listado / Devolución</Link>
                        </div>
                    </li>

                    <!-- Avisador -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Avisador</a>
                        <div class="dropdown-menu">
                            <Link :href="route('noticias.create')" class="dropdown-item">Nueva noticia</Link>
                            <Link :href="route('noticias.index')" class="dropdown-item">Modificar / Eliminar</Link>
                        </div>
                    </li>

                    <!-- Notas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Notas</a>
                        <div class="dropdown-menu">
                            <Link :href="route('anotaciones.create')" class="dropdown-item">Nueva nota</Link>
                            <Link :href="route('anotaciones.index')" class="dropdown-item">Consultar</Link>
                        </div>
                    </li>
                </ul>

                <!-- Alerta préstamos + perfil -->
                <ul class="navbar-nav ml-auto align-items-center">
                    <li v-if="vencimientosProximos > 0" class="nav-item mr-2">
                        <Link :href="route('prestamos.index', { estado: 'activo' })" class="btn btn-warning btn-sm">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            {{ vencimientosProximos }} vencimiento{{ vencimientosProximos > 1 ? 's' : '' }}
                        </Link>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" href="#">
                            <template v-if="auth.user.picture_url">
                                <img :src="auth.user.picture_url" alt="perfil" class="perfil-img mr-2">
                            </template>
                            <template v-else>
                                <i class="bi bi-person-circle" style="font-size: 1.5rem; color: var(--eliber-accent);"></i>
                            </template>
                            <span class="ms-2">{{ auth.user.nombre }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="border-radius: 10px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                            <h6 class="dropdown-header" style="color: var(--eliber-primary); font-weight: 600;">{{ auth.user.usuario }}</h6>
                            <div class="dropdown-divider"></div>
                            <form @submit.prevent="logout" method="POST">
                                <button type="submit" class="dropdown-item text-danger" style="transition: all 0.3s ease;">
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
import { usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const auth = computed(() => page.props.auth)
const vencimientosProximos = computed(() => page.props.vencimientos_proximos ?? 0)

function logout() {
    router.post(route('logout'))
}
</script>

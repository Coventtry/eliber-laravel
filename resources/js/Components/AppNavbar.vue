<template>
    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <Link :href="route('dashboard')" class="navbar-brand font-weight-bold">
                <i class="bi bi-book-half mr-2"></i>E-liber
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
                            <img :src="auth.user.picture_url" alt="perfil" class="perfil-img mr-2">
                            <span>{{ auth.user.nombre }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h6 class="dropdown-header">{{ auth.user.usuario }}</h6>
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
import { usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const auth = computed(() => page.props.auth)
const vencimientosProximos = computed(() => page.props.vencimientos_proximos ?? 0)

function logout() {
    router.post(route('logout'))
}
</script>

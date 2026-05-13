<template>
    <!-- Banner de anuncio -->
    <div v-if="anuncio" :class="`alert alert-${anuncio.estilo} mb-0 py-2 text-center`"
         style="border-radius:0;font-size:.85rem;border-left:none;border-right:none;">
        <i class="bi bi-megaphone mr-2"></i>{{ anuncio.texto }}
    </div>

    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <Link :href="route('dashboard')" class="navbar-brand">
                <img src="/img/logo.png" alt="E-liber" height="36">
            </Link>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain"
                    aria-controls="navMain" aria-expanded="false" aria-label="Abrir menú de navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mr-auto">
                    <!-- Socios -->
                    <li v-if="can('gestionar-socios')" class="nav-item">
                        <Link :href="route('socios.index')" class="nav-link">
                            <i class="bi bi-people mr-1"></i>Socios
                        </Link>
                    </li>

                    <!-- Materiales -->
                    <li v-if="can('gestionar-materiales')" class="nav-item dropdown">
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
                    <li v-if="can('gestionar-areas')" class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Áreas</a>
                        <div class="dropdown-menu">
                            <Link :href="route('areas.create')" class="dropdown-item">Nueva área</Link>
                            <Link :href="route('areas.index')" class="dropdown-item">Modificar / Eliminar</Link>
                        </div>
                    </li>

                    <!-- Préstamos -->
                    <li v-if="can('gestionar-prestamos')" class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Préstamos</a>
                        <div class="dropdown-menu">
                            <Link :href="route('prestamos.create')" class="dropdown-item">
                                <i class="bi bi-journal-plus mr-1"></i>Terminal de préstamos
                            </Link>
                            <div class="dropdown-divider"></div>
                            <Link :href="route('prestamos.index')" class="dropdown-item">
                                <i class="bi bi-list-ul mr-1"></i>Listado / Devolución
                            </Link>
                            <div class="dropdown-divider"></div>
                            <Link :href="route('prestamos.solicitudes')" class="dropdown-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-envelope-open mr-1"></i>Solicitudes</span>
                                <span v-if="solicitudesPendientes > 0" class="badge badge-warning ml-2">{{ solicitudesPendientes }}</span>
                            </Link>
                        </div>
                    </li>

                    <!-- Noticias -->
                    <li v-if="can('gestionar-noticias')" class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Noticias</a>
                        <div class="dropdown-menu">
                            <Link :href="route('noticias.create')" class="dropdown-item">Nueva noticia</Link>
                            <Link :href="route('noticias.index')" class="dropdown-item">Modificar / Eliminar</Link>
                        </div>
                    </li>


                    <!-- Usuarios -->
                    <li v-if="can('gestionar-usuarios')" class="nav-item">
                        <Link :href="route('usuarios.index')" class="nav-link">
                            <i class="bi bi-person-check mr-1"></i>Usuarios
                        </Link>
                    </li>

                    <!-- Alertas -->
                    <li class="nav-item">
                        <Link :href="route('alertas.index')" class="nav-link">
                            Alertas
                            <span v-if="alertasNoLeidas > 0" class="badge badge-danger ml-1">{{ alertasNoLeidas }}</span>
                        </Link>
                    </li>
                </ul>

                <!-- Alerta préstamos + perfil -->
                <ul class="navbar-nav ml-auto align-items-center">
                    <!-- Dark mode toggle -->
                    <li class="nav-item mr-2">
<button class="dm-toggle" @click="toggleDark" :title="darkMode ? 'Modo claro' : 'Modo oscuro'" :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'">
                        <i :class="darkMode ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
                    </button>
                    </li>

                    <li v-if="vencimientosProximos > 0" class="nav-item mr-2">
                        <Link :href="route('prestamos.index', { estado: 'activo' })" class="btn btn-warning btn-sm">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            {{ vencimientosProximos }} vencimiento{{ vencimientosProximos > 1 ? 's' : '' }}
                        </Link>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" href="#">
                            <img v-if="auth.user.picture_url"
                                :src="auth.user.picture_url"
                                alt="perfil"
                                class="perfil-img mr-2">
                             <div v-else
                                 class="nav-avatar-initials mr-2">
                                 {{ auth.user.nombre?.charAt(0)?.toUpperCase() }}
                             </div>
                             <div class="d-none d-md-flex flex-column nav-user-line">
                                 <span class="nav-user-name">{{ auth.user.nombre }}</span>
                                 <span class="nav-user-role">{{ labelRol }}</span>
                             </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown-menu">
                             <h6 class="dropdown-header font-weight-bold" style="color:var(--eliber-primary);">{{ auth.user.usuario }}</h6>
                            <div class="dropdown-divider"></div>
                            <Link :href="route('perfil.edit')" class="dropdown-item">
                                <i class="bi bi-person-gear mr-1"></i>Mi perfil
                            </Link>
                            <Link v-if="can('gestionar-anotaciones')" :href="route('anotaciones.index')" class="dropdown-item">
                                <i class="bi bi-journal-text mr-1"></i>Anotaciones
                            </Link>
                            <div class="dropdown-divider"></div>
                            <form @submit.prevent="logout" method="POST">
                                <button type="submit" class="dropdown-item text-danger" :disabled="cargando">
                                    <span v-if="cargando" class="spinner-border spinner-border-sm mr-1"></span>
                                    <i v-else class="bi bi-box-arrow-right mr-1"></i>
                                    Cerrar sesión
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
import { usePage, router, Link } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { initDarkMode, useDarkMode } from '@/Composables/useDarkMode'

const page    = usePage()
const cargando = ref(false)
const auth    = computed(() => page.props.auth)

const { darkMode, toggleDark } = useDarkMode()
onMounted(() => initDarkMode(auth.value?.user?.id ?? null))
const anuncio = computed(() => page.props.anuncio ?? null)
const vencimientosProximos = computed(() => page.props.vencimientos_proximos ?? 0)
const alertasNoLeidas = computed(() => page.props.alertas_no_leidas ?? 0)
const solicitudesPendientes = computed(() => page.props.solicitudes_pendientes ?? 0)

const ROLES = { admin: 'Administrador', bibliotecario: 'Bibliotecario', alumno: 'Alumno' }
const labelRol = computed(() => {
    const rol = page.props.auth?.roles?.[0]
    return ROLES[rol] ?? rol ?? ''
})

function can(permiso) {
    if (auth.value?.es_admin) return true
    return auth.value?.permisos?.includes(permiso) ?? false
}

function logout() {
    router.post(route('logout'), {}, {
        onStart: () => { cargando.value = true },
    })
}

</script>

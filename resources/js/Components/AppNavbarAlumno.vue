<template>
    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <Link :href="route('alumno.dashboard')" class="navbar-brand">
                <img src="/img/logo.png" alt="E-liber" height="36">
            </Link>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navAlumno"
                    aria-controls="navAlumno" aria-expanded="false" aria-label="Abrir menú de navegación">
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
                        <Link :href="route('alumno.prestamos')" class="nav-link">
                            <i class="bi bi-book mr-1"></i>Mis préstamos
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link :href="route('alumno.reservas')" class="nav-link">
                            <i class="bi bi-bookmark mr-1"></i>Mis reservas
                        </Link>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item mr-2">
<button class="dm-toggle" @click="toggleDark" :title="darkMode ? 'Modo claro' : 'Modo oscuro'" :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'">
                        <i :class="darkMode ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
                    </button>
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
                                 <span class="nav-user-role">Alumno</span>
                             </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown-menu">
                             <h6 class="dropdown-header font-weight-bold" style="color:var(--eliber-primary);">{{ auth.user.usuario }}</h6>
                            <div class="dropdown-divider"></div>
                            <Link :href="route('perfil.edit')" class="dropdown-item">
                                <i class="bi bi-person-gear mr-1"></i>Mi perfil
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
import { usePage, Link, router } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { initDarkMode, useDarkMode } from '@/Composables/useDarkMode'

const page    = usePage()
const cargando = ref(false)
const auth = computed(() => page.props.auth)

const { darkMode, toggleDark } = useDarkMode()
onMounted(() => initDarkMode(auth.value?.user?.id ?? null))

function logout() {
    router.post(route('logout'), {}, {
        onStart: () => { cargando.value = true },
    })
}
</script>

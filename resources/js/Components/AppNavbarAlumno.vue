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
                    <li class="nav-item mr-2">
                        <button class="dm-toggle" @click="toggleDark" :title="darkMode ? 'Modo claro' : 'Modo oscuro'">
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
                                class="mr-2"
                                style="width:36px;height:36px;border-radius:50%;background:var(--eliber-accent,#e8a020);color:#fff;font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid rgba(255,255,255,.3);">
                                {{ auth.user.nombre?.charAt(0)?.toUpperCase() }}
                            </div>
                            <div class="d-none d-md-flex flex-column" style="line-height:1.2;">
                                <span class="text-white font-weight-bold" style="font-size:.85rem;">{{ auth.user.nombre }}</span>
                                <span style="font-size:.7rem;color:rgba(255,255,255,.65);">Alumno</span>
                            </div>
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
import { computed, onMounted } from 'vue'
import { initDarkMode, useDarkMode } from '@/Composables/useDarkMode'

const page = usePage()
const auth = computed(() => page.props.auth)

const { darkMode, toggleDark } = useDarkMode()
onMounted(() => initDarkMode(auth.value?.user?.id ?? null))

function logout() {
    router.post(route('logout'))
}
</script>

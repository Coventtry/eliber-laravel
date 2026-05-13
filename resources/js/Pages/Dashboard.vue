<template>
    <Head title="Inicio" />
    <AppNavbar />

    <div class="hero-section text-center" style="background: linear-gradient(rgba(27, 94, 32, 0.85), rgba(27, 94, 32, 0.85)), url('/img/menu_bibliotecario.jpg') center/cover no-repeat;">
        <div class="container">
            <img
                :src="$page.props.logo_url || '/img/logo.png'"
                alt="Logo institucional"
                class="mb-3"
                style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.8);background:#fff;"
            >
            <h2 class="font-weight-bold">Bienvenido, {{ $page.props.auth.user.nombre }}</h2>
            <p class="lead">Sistema de Gestión de Biblioteca Escolar</p>
        </div>
    </div>

    <div class="container page-content">
        <FlashMessage />

        <!-- Solicitudes pendientes -->
        <div v-if="solicitudesPendientes > 0" class="alert alert-info mt-3">
            <Link :href="route('prestamos.solicitudes')" class="text-decoration-none">
                <i class="bi bi-envelope-open mr-2"></i>
                <strong>{{ solicitudesPendientes }}</strong> solicitud{{ solicitudesPendientes > 1 ? 'es' : '' }} de reserva pendiente{{ solicitudesPendientes > 1 ? 's' : '' }} de aprobación
                <i class="bi bi-arrow-right ml-2"></i>
            </Link>
        </div>

        <!-- Alertas de vencimientos -->
        <div v-if="vencimientosProximos.length" class="alert alert-warning mt-3">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                Préstamos próximos a vencer
            </h5>
            <ul class="mb-0">
                <li v-for="p in vencimientosProximos" :key="p.id">
                    <strong>{{ p.socio }}</strong> — {{ p.material }}
                    (vence {{ p.fecha_devolucion }})
                    <a v-if="p.link_whatsapp" :href="p.link_whatsapp" target="_blank"
                       class="btn btn-success btn-sm ml-2">
                        <i class="bi bi-whatsapp"></i> Recordar
                    </a>
                </li>
            </ul>
        </div>

        <!-- Accesos rápidos -->
        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <Link :href="route('prestamos.create')" class="btn btn-success btn-block py-3">
                    <i class="bi bi-journal-plus d-block icon-2xl"></i>
                    Nuevo préstamo
                </Link>
            </div>
            <div class="col-md-4 mb-3">
                <Link :href="route('socios.create')" class="btn btn-outline-success btn-block py-3">
                    <i class="bi bi-person-plus d-block icon-2xl"></i>
                    Nuevo socio
                </Link>
            </div>
            <div class="col-md-4 mb-3">
                <Link :href="route('materiales.create')" class="btn btn-outline-success btn-block py-3">
                    <i class="bi bi-book-plus d-block icon-2xl"></i>
                    Nuevo material
                </Link>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    vencimientosProximos: { type: Array, default: () => [] },
})

const page = usePage()
const solicitudesPendientes = computed(() => page.props.solicitudes_pendientes ?? 0)
</script>

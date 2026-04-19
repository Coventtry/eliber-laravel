<template>
    <Head title="Inicio" />
    <AppNavbar />

    <div class="hero-section text-center">
        <h2 class="font-weight-bold">Bienvenido, {{ $page.props.auth.user.nombre }}</h2>
        <p class="lead">Sistema de Gestión de Biblioteca Escolar</p>
    </div>

    <div class="container page-content">
        <FlashMessage />

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
                    <i class="bi bi-journal-plus d-block" style="font-size:1.8rem;"></i>
                    Nuevo préstamo
                </Link>
            </div>
            <div class="col-md-4 mb-3">
                <Link :href="route('socios.create')" class="btn btn-outline-success btn-block py-3">
                    <i class="bi bi-person-plus d-block" style="font-size:1.8rem;"></i>
                    Nuevo socio
                </Link>
            </div>
            <div class="col-md-4 mb-3">
                <Link :href="route('materiales.create')" class="btn btn-outline-success btn-block py-3">
                    <i class="bi bi-book-plus d-block" style="font-size:1.8rem;"></i>
                    Nuevo material
                </Link>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    vencimientosProximos: { type: Array, default: () => [] },
})
</script>

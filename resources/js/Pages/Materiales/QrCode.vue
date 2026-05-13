<template>
    <Head :title="`QR - ${material.codigo}`" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container text-center" style="max-width: 440px; margin: auto;">

            <h4 class="mb-1">{{ material.titulo }}</h4>
            <p v-if="material.autor" class="text-muted small mb-1">{{ material.autor }}</p>
            <code class="d-block mb-1">{{ material.codigo }}</code>

            <div v-if="material.clasificacion_fisica"
                 class="alert alert-warning d-inline-block px-3 py-1 mb-3"
                 style="font-family:monospace;font-size:1.1rem;font-weight:700;letter-spacing:.05em;">
                <i class="bi bi-geo-alt-fill mr-1"></i>{{ material.clasificacion_fisica }}
            </div>

            <div>
                <img :src="qrUrl" alt="Código QR" class="img-fluid border p-2 mb-3" style="max-width:280px;">
            </div>

            <div class="d-flex justify-content-center" style="gap:.5rem;flex-wrap:wrap;">
                <a :href="qrUrl" download class="btn btn-success">
                    <i class="bi bi-download mr-1"></i>Descargar
                </a>
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer mr-1"></i>Imprimir
                </button>
                <Link :href="route('materiales.index')" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left mr-1"></i>Volver
                </Link>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'

defineProps({
    material: { type: Object, required: true },
    qrUrl:    { type: String, required: true },
})
</script>

<style>
@media print {
    .navbar, footer, .btn, nav { display: none !important; }
    .main-container { text-align: center; }
}
</style>

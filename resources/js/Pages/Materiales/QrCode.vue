<template>
    <Head :title="`QR - ${material.codigo}`" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container text-center" style="max-width:420px;">
            <h4 class="mb-1">{{ material.titulo }}</h4>
            <code class="d-block mb-3">{{ material.codigo }}</code>

            <img :src="qrUrl" alt="Código QR" class="img-fluid border p-2 mb-3" style="max-width: 300px;">

            <div>
                <a :href="qrUrl" download class="btn btn-success mr-2">
                    <i class="bi bi-download mr-1"></i>Descargar
                </a>
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

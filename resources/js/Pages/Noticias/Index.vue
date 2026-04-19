<template>
    <Head title="Noticias" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="bi bi-newspaper mr-2"></i>Noticias</h3>
                <Link :href="route('noticias.create')" class="btn btn-success">
                    <i class="bi bi-plus-lg mr-1"></i>Nueva
                </Link>
            </div>
            <FlashMessage />

            <div class="row">
                <div v-for="n in noticias.data" :key="n.id" class="col-md-4 mb-4">
                    <div class="card card-noticia h-100">
                        <img v-if="n.imagen_url" :src="n.imagen_url" class="card-img-top"
                             style="height: 180px; object-fit: cover;" alt="">
                        <div class="card-body">
                            <h5 class="card-title">{{ n.titulo }}</h5>
                            <p class="card-text text-muted small">{{ n.descripcion }}</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ n.fecha }}</small>
                            <div>
                                <Link :href="route('noticias.edit', n.id)" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                                <button class="btn btn-sm btn-outline-danger" @click="eliminar(n)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!noticias.data.length" class="col-12 text-center text-muted py-5">
                    No hay noticias publicadas.
                </div>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({ noticias: { type: Object, required: true } })

function eliminar(n) {
    if (confirm(`¿Eliminar "${n.titulo}"?`)) {
        router.delete(route('noticias.destroy', n.id))
    }
}
</script>

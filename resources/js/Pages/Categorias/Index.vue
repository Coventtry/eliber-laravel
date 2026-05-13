<template>
    <Head title="Categorías de material" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-bookmark mr-2"></i>Categorías de material</h3>
                <Link :href="route('categorias.create')" class="btn btn-success">
                    <i class="bi bi-plus-lg mr-1"></i>Nueva
                </Link>
            </div>
            <FlashMessage />

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Nombre</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="c in categorias" :key="c.id">
                            <td>{{ c.nombre }}</td>
                            <td class="text-right">
                                <Link :href="route('categorias.edit', c.id)" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                                <button class="btn btn-sm btn-outline-danger" @click="eliminar(c)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!categorias.length">
                            <td colspan="2" class="text-muted text-center">Sin categorías. Creá la primera.</td>
                        </tr>
                    </tbody>
                </table>
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

defineProps({ categorias: { type: Array, required: true } })

function eliminar(c) {
    if (confirm(`¿Eliminar la categoría "${c.nombre}"?`)) {
        router.delete(route('categorias.destroy', c.id))
    }
}
</script>

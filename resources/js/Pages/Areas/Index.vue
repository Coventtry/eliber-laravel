<template>
    <Head title="Áreas" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-tag mr-2"></i>Áreas</h3>
                <Link :href="route('areas.create')" class="btn btn-success">
                    <i class="bi bi-plus-lg mr-1"></i>Nueva
                </Link>
            </div>
            <FlashMessage />

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Código Dewey</th><th>Nombre</th><th>Abreviado</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="a in areas.data" :key="a.id">
                            <td><code>{{ a.codigo_dewey }}</code></td>
                            <td>{{ a.nombre }}</td>
                            <td>{{ a.Abreviado }}</td>
                            <td class="text-right">
                                <Link :href="route('areas.edit', a.id)" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                                <button class="btn btn-sm btn-outline-danger" @click="eliminar(a)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
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

defineProps({ areas: { type: Object, required: true } })

function eliminar(area) {
    if (confirm(`¿Eliminar el área "${area.nombre}"?`)) {
        router.delete(route('areas.destroy', area.id))
    }
}
</script>

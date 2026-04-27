<template>
    <Head title="Categorías de material" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width: 560px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-bookmark mr-2"></i>Categorías de material</h3>
            <FlashMessage />

            <!-- Formulario agregar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Agregar categoría</h6>
                    <form @submit.prevent="submit" class="d-flex" style="gap:.5rem;">
                        <div class="flex-grow-1">
                            <input v-model="form.nombre" type="text" class="form-control"
                                   :class="{ 'is-invalid': form.errors.nombre }"
                                   placeholder="Ej: Libro, Manual, Diccionario…">
                            <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                        </div>
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Listado existente -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li v-for="c in categorias" :key="c.id"
                            class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ c.nombre }}</span>
                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(c)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </li>
                        <li v-if="!categorias.length" class="list-group-item text-muted text-center">
                            Sin categorías todavía.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <AppFooter />
</template>

<script setup>
import { router, useForm, Link } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({ categorias: { type: Array, default: () => [] } })

const form = useForm({ nombre: '' })

function submit() {
    form.post(route('categorias.store'), {
        onSuccess: () => form.reset(),
    })
}

function eliminar(c) {
    if (confirm(`¿Eliminar "${c.nombre}"?`)) {
        router.delete(route('categorias.destroy', c.id))
    }
}
</script>

<template>
    <Head title="Nueva categoría" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width: 560px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-tag-fill mr-2"></i>Nueva categoría</h3>
            <FlashMessage />
            <form @submit.prevent="submit">
                <div class="form-group">
                    <label>Código Dewey <span class="text-danger">*</span></label>
                    <input v-model="form.codigo_dewey" type="text" class="form-control"
                           :class="{ 'is-invalid': form.errors.codigo_dewey }">
                    <div class="invalid-feedback">{{ form.errors.codigo_dewey }}</div>
                </div>
                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input v-model="form.nombre" type="text" class="form-control"
                           :class="{ 'is-invalid': form.errors.nombre }">
                    <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                </div>
                <div class="form-group">
                    <label>Abreviado</label>
                    <input v-model="form.Abreviado" type="text" class="form-control">
                </div>
                <div class="d-flex justify-content-between">
                    <Link :href="route('areas.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <AppFooter />
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const form = useForm({ codigo_dewey: '', nombre: '', Abreviado: '' })
const submit = () => form.post(route('areas.store'))
</script>

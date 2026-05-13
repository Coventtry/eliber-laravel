<template>
    <Head title="Nueva nota" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width:560px;">
            <h3 class="mb-4"><i class="bi bi-journal-plus mr-2"></i>Nueva nota</h3>
            <form @submit.prevent="submit">
                <div class="form-group">
                    <label>Nota <span class="text-danger">*</span></label>
                    <textarea v-model="form.anotacion" rows="4" class="form-control"
                              :class="{ 'is-invalid': form.errors.anotacion }" maxlength="255"></textarea>
                    <div class="invalid-feedback">{{ form.errors.anotacion }}</div>
                </div>
                <div class="d-flex justify-content-between">
                    <Link :href="route('anotaciones.index')" class="btn btn-outline-secondary">Cancelar</Link>
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

const form   = useForm({ anotacion: '' })
const submit = () => form.post(route('anotaciones.store'))
</script>

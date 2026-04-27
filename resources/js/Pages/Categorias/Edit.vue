<template>
    <Head title="Editar categoría" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width: 480px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-bookmark mr-2"></i>Editar categoría</h3>
            <FlashMessage />
            <form @submit.prevent="submit">
                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input v-model="form.nombre" type="text" class="form-control"
                           :class="{ 'is-invalid': form.errors.nombre }">
                    <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                </div>
                <div class="d-flex justify-content-between">
                    <Link :href="route('categorias.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    <AppFooter />
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({ categoria: { type: Object, required: true } })
const form  = useForm({ nombre: props.categoria.nombre })
const submit = () => form.put(route('categorias.update', props.categoria.id))
</script>

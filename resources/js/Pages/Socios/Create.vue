<template>
    <Head title="Nuevo socio" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 640px; margin-left: auto; margin-right: auto;">
            <h3 class="mb-4"><i class="bi bi-person-plus mr-2"></i>Nuevo socio</h3>
            <FlashMessage />

            <form @submit.prevent="enviar">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input v-model="form.nombre" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.nombre }">
                        <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Apellido <span class="text-danger">*</span></label>
                        <input v-model="form.apellido" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.apellido }">
                        <div class="invalid-feedback">{{ form.errors.apellido }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input v-model="form.email" type="email" class="form-control"
                           :class="{ 'is-invalid': form.errors.email }">
                    <div class="invalid-feedback">{{ form.errors.email }}</div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Teléfono</label>
                        <input v-model="form.telefono" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Dirección</label>
                        <input v-model="form.direccion" type="text" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Año (1–6)</label>
                        <input v-model.number="form.anio" type="number" min="1" max="6" class="form-control"
                               :class="{ 'is-invalid': form.errors.anio }">
                        <div class="invalid-feedback">{{ form.errors.anio }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>División (1–6)</label>
                        <input v-model.number="form.division" type="number" min="1" max="6" class="form-control"
                               :class="{ 'is-invalid': form.errors.division }">
                        <div class="invalid-feedback">{{ form.errors.division }}</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <Link :href="route('socios.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm mr-1"></span>
                        Guardar
                    </button>
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

const form = useForm({
    nombre: '', apellido: '', email: '',
    telefono: '', direccion: '', anio: null, division: null,
})

function enviar() {
    form.post(route('socios.store'))
}
</script>

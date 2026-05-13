<template>
    <Head title="Nueva multa" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width:560px;">
            <h3 class="mb-4"><i class="bi bi-currency-dollar mr-2"></i>Nueva multa</h3>
            <FlashMessage />
            <form @submit.prevent="submit">
                <div class="form-group">
                    <label>Socio <span class="text-danger">*</span></label>
                    <select v-model="form.socio_id" class="form-control"
                            :class="{ 'is-invalid': form.errors.socio_id }">
                        <option value="">Seleccionar socio...</option>
                        <option v-for="s in socios" :key="s.id" :value="s.id">{{ s.apellido }}, {{ s.nombre }}</option>
                    </select>
                    <div class="invalid-feedback">{{ form.errors.socio_id }}</div>
                </div>
                <div class="form-group">
                    <label>Monto ($) <span class="text-danger">*</span></label>
                    <input v-model="form.monto" type="number" step="0.01" min="0.01" class="form-control"
                           :class="{ 'is-invalid': form.errors.monto }">
                    <div class="invalid-feedback">{{ form.errors.monto }}</div>
                </div>
                <div class="form-group">
                    <label>Motivo <span class="text-danger">*</span></label>
                    <input v-model="form.motivo" type="text" class="form-control" maxlength="255"
                           :class="{ 'is-invalid': form.errors.motivo }">
                    <div class="invalid-feedback">{{ form.errors.motivo }}</div>
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea v-model="form.observaciones" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <Link :href="route('multas.index')" class="btn btn-outline-secondary">Cancelar</Link>
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

defineProps({ socios: { type: Array, required: true } })

const form = useForm({ socio_id: '', monto: '', motivo: '', observaciones: '' })
const submit = () => form.post(route('multas.store'))
</script>

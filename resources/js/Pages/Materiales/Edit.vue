<template>
    <Head :title="`Editar: ${material.titulo}`" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 760px; margin: auto;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="bi bi-pencil-square mr-2"></i>Editar material</h3>
                <Link v-if="qrUrl" :href="route('materiales.qr', material.id)" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-qr-code mr-1"></i>Ver QR
                </Link>
            </div>
            <FlashMessage />

            <form @submit.prevent="submit">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Título <span class="text-danger">*</span></label>
                        <input v-model="form.titulo" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.titulo }">
                        <div class="invalid-feedback">{{ form.errors.titulo }}</div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Año de publicación</label>
                        <input v-model.number="form.anio_publicacion" type="number" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Autor</label>
                        <input v-model="form.autor" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Editorial</label>
                        <input v-model="form.editorial" type="text" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Área <span class="text-danger">*</span></label>
                        <select v-model.number="form.area_id" class="form-control">
                            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Categoría</label>
                        <input v-model="form.categoria" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Disponibilidad</label>
                        <input v-model.number="form.disponibilidad" type="number" min="0" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Código</label>
                    <input type="text" class="form-control-plaintext font-weight-bold" :value="material.codigo" readonly>
                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        <Link :href="route('materiales.index')" class="btn btn-outline-secondary mr-2">Cancelar</Link>
                        <button type="button" class="btn btn-outline-danger" @click="eliminar">Eliminar</button>
                    </div>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    material: { type: Object, required: true },
    areas:    { type: Array, default: () => [] },
    qrUrl:    { type: String, default: null },
})

const form = useForm({
    titulo: props.material.titulo,
    autor: props.material.autor,
    anio_publicacion: props.material.anio_publicacion,
    area_id: props.material.area_id,
    categoria: props.material.categoria,
    disponibilidad: props.material.disponibilidad,
    editorial: props.material.editorial,
})

function submit() {
    form.put(route('materiales.update', props.material.id))
}

function eliminar() {
    if (confirm(`¿Eliminar "${props.material.titulo}"?`)) {
        router.delete(route('materiales.destroy', props.material.id))
    }
}
</script>

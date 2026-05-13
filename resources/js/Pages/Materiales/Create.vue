<template>
    <Head title="Nuevo material" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width:760px;">
            <h3 class="mb-4"><i class="bi bi-book-plus mr-2"></i>Nuevo material</h3>
            <FlashMessage />

            <form @submit.prevent="enviar">
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
                        <select v-model.number="form.area_id" class="form-control"
                                :class="{ 'is-invalid': form.errors.area_id }"
                                @change="actualizarCodigo">
                            <option value="">— Seleccioná —</option>
                            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                        </select>
                        <div class="invalid-feedback">{{ form.errors.area_id }}</div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Categoría</label>
                        <input v-model="form.categoria" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Disponibilidad <span class="text-danger">*</span></label>
                        <input v-model.number="form.disponibilidad" type="number" min="0" class="form-control">
                    </div>
                </div>

                <!-- Código (read-only, generado automáticamente) -->
                <div class="form-group" v-if="codigoGenerado">
                    <label>Código asignado</label>
                    <input type="text" class="form-control-plaintext font-weight-bold" :value="codigoGenerado" readonly>
                </div>

                <!-- Clasificación física -->
                <fieldset class="border p-3 mb-3">
                    <legend class="w-auto px-2 h6">Ubicación física (opcional)</legend>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Pasillo</label>
                            <input v-model="form.pasillo" type="text" class="form-control" maxlength="2"
                                   placeholder="A-Z" @input="actualizarClasificacion">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tipo</label>
                            <select v-model="form.tipo_almacenamiento" class="form-control"
                                    @change="actualizarClasificacion">
                                <option value="E">Estante (E)</option>
                                <option value="M">Mueble (M)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Estante (1-30)</label>
                            <input v-model.number="form.estante" type="number" min="1" max="30" class="form-control"
                                   @input="actualizarClasificacion">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Nivel (1-6)</label>
                            <input v-model.number="form.nivel" type="number" min="1" max="6" class="form-control"
                                   @input="actualizarClasificacion">
                        </div>
                    </div>
                    <div v-if="previsualizacionClasificacion" class="alert alert-light">
                        Código de ubicación: <strong>{{ previsualizacionClasificacion }}</strong>
                    </div>
                </fieldset>

                <div class="d-flex justify-content-between">
                    <Link :href="route('materiales.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({ areas: { type: Array, default: () => [] } })

const form = useForm({
    titulo: '', autor: '', anio_publicacion: null, area_id: '',
    categoria: '', disponibilidad: 1, editorial: '',
    pasillo: '', tipo_almacenamiento: 'E', estante: null, nivel: null,
})

const codigoGenerado      = ref('')
const previsualizacionClasificacion = ref('')

async function actualizarCodigo() {
    if (!form.area_id) return
    try {
        const { data } = await axios.get(route('api.materiales.ultimo-codigo'), { params: { area_id: form.area_id } })
        codigoGenerado.value = data.codigo
        actualizarClasificacion()
    } catch {
        alert('No se pudo generar el código.')
    }
}

function actualizarClasificacion() {
    if (!form.area_id || !form.pasillo || !form.estante || !form.nivel) {
        previsualizacionClasificacion.value = ''
        return
    }
    const area  = props.areas.find(a => a.id === form.area_id)
    const abrev = area?.Abreviado ?? area?.codigo_dewey ?? ''
    previsualizacionClasificacion.value = `${abrev}-${form.pasillo}-(${form.tipo_almacenamiento})${form.estante}-${form.nivel}`
}

function enviar() {
    form.post(route('materiales.store'))
}
</script>

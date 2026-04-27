<template>
    <Head title="Nuevo préstamo" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 720px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-journal-plus mr-2"></i>Terminal de Préstamos</h3>
            <FlashMessage />

            <!-- Paso 1: buscar socio -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Paso 1 — Identificar socio</div>
                <div class="card-body">
                    <div class="input-group">
                        <input v-model="terminoBusquedaSocio" type="text" class="form-control"
                               placeholder="Nombre, apellido o email del socio…"
                               @input="buscarSocios"
                               :disabled="!!form.socio_id">
                        <div class="input-group-append" v-if="form.socio_id">
                            <button type="button" class="btn btn-outline-secondary" @click="limpiarSocio">
                                <i class="bi bi-x-lg"></i> Cambiar
                            </button>
                        </div>
                    </div>
                    <ul v-if="sociosEncontrados.length && !form.socio_id" class="list-group mt-2">
                        <button v-for="s in sociosEncontrados" :key="s.id" type="button"
                                class="list-group-item list-group-item-action"
                                @click="seleccionarSocio(s)">
                            <strong>{{ s.apellido ? `${s.apellido}, ${s.nombre}` : s.nombre }}</strong>
                            <span class="ml-2">{{ s.email }}</span>
                            <small class="ml-2" v-if="s.anio">— {{ s.anio }}° {{ s.division }}</small>
                        </button>
                    </ul>
                    <div v-if="form.socio_id" class="alert alert-success mb-0 mt-2">
                        <i class="bi bi-person-check mr-1"></i>
                        <strong>{{ socioSeleccionado?.apellido ? `${socioSeleccionado.apellido}, ${socioSeleccionado.nombre}` : socioSeleccionado?.nombre }}</strong>
                        <span class="ml-2">{{ socioSeleccionado?.email }}</span>
                    </div>
                </div>
            </div>

            <!-- Paso 2: formulario -->
            <form v-if="form.socio_id" @submit.prevent="enviar">
                <!-- Búsqueda predictiva de material -->
                <div class="form-group">
                    <label>Material <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input v-model="terminoBusquedaMaterial" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.material_id }"
                               placeholder="Buscar por título o código…"
                               @input="buscarMateriales"
                               :disabled="!!form.material_id">
                        <div class="input-group-append" v-if="form.material_id">
                            <button type="button" class="btn btn-outline-secondary" @click="limpiarMaterial">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <ul v-if="materialesEncontrados.length && !form.material_id" class="list-group mt-1">
                        <button v-for="m in materialesEncontrados" :key="m.id" type="button"
                                class="list-group-item list-group-item-action"
                                @click="seleccionarMaterial(m)">
                            <strong>{{ m.titulo }}</strong>
                            <small class="text-muted ml-2">{{ m.codigo }}</small>
                            <span class="badge badge-info ml-2">{{ m.disponibilidad }} disp.</span>
                        </button>
                    </ul>
                    <div v-if="materialSeleccionado" class="alert alert-info py-2 mb-0 mt-1">
                        <i class="bi bi-book mr-1"></i>
                        <strong>{{ materialSeleccionado.titulo }}</strong>
                        <small class="ml-2">{{ materialSeleccionado.codigo }}</small>
                        <span class="badge badge-info ml-2">{{ materialSeleccionado.disponibilidad }} disponible{{ materialSeleccionado.disponibilidad !== 1 ? 's' : '' }}</span>
                    </div>
                    <div class="invalid-feedback d-block" v-if="form.errors.material_id">{{ form.errors.material_id }}</div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Cantidad <span class="text-danger">*</span></label>
                        <input v-model.number="form.cantidad" type="number" min="1" class="form-control"
                               :class="{ 'is-invalid': form.errors.cantidad }">
                        <div class="invalid-feedback">{{ form.errors.cantidad }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Fecha de devolución <span class="text-danger">*</span></label>
                        <input v-model="form.fecha_devolucion" type="date" class="form-control"
                               :min="hoy" :max="maxFecha"
                               :class="{ 'is-invalid': form.errors.fecha_devolucion }">
                        <div class="invalid-feedback">{{ form.errors.fecha_devolucion }}</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <Link :href="route('prestamos.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing || !form.material_id">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="bi bi-check-circle mr-1"></i>
                        Confirmar préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    areas:        { type: Array, default: () => [] },
    socioInicial: { type: Object, default: null },
})

const terminoBusquedaSocio    = ref('')
const sociosEncontrados       = ref([])
const socioSeleccionado       = ref(null)

const terminoBusquedaMaterial = ref('')
const materialesEncontrados   = ref([])
const materialSeleccionado    = ref(null)

const hoy      = new Date().toISOString().split('T')[0]
const maxFecha = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0]

const form = useForm({
    socio_id:         '',
    material_id:      '',
    cantidad:         1,
    fecha_devolucion: hoy,
})

onMounted(() => {
    if (props.socioInicial) {
        seleccionarSocio(props.socioInicial)
    }
})

let timeoutSocio = null
async function buscarSocios() {
    clearTimeout(timeoutSocio)
    if (terminoBusquedaSocio.value.length < 2) { sociosEncontrados.value = []; return }
    timeoutSocio = setTimeout(async () => {
        const { data } = await axios.get(route('api.socios.buscar'), { params: { q: terminoBusquedaSocio.value } })
        sociosEncontrados.value = data
    }, 300)
}

function seleccionarSocio(s) {
    socioSeleccionado.value       = s
    form.socio_id                 = s.id
    sociosEncontrados.value       = []
    terminoBusquedaSocio.value    = s.apellido ? `${s.apellido}, ${s.nombre}` : s.nombre
}

function limpiarSocio() {
    form.socio_id              = ''
    socioSeleccionado.value    = null
    terminoBusquedaSocio.value = ''
    sociosEncontrados.value    = []
    limpiarMaterial()
}

let timeoutMaterial = null
async function buscarMateriales() {
    clearTimeout(timeoutMaterial)
    if (terminoBusquedaMaterial.value.length < 2) { materialesEncontrados.value = []; return }
    timeoutMaterial = setTimeout(async () => {
        const { data } = await axios.get(route('api.materiales.disponibles'), {
            params: { busqueda: terminoBusquedaMaterial.value },
        })
        materialesEncontrados.value = data
    }, 300)
}

function seleccionarMaterial(m) {
    materialSeleccionado.value       = m
    form.material_id                 = m.id
    materialesEncontrados.value      = []
    terminoBusquedaMaterial.value    = m.titulo
}

function limpiarMaterial() {
    form.material_id              = ''
    materialSeleccionado.value    = null
    terminoBusquedaMaterial.value = ''
    materialesEncontrados.value   = []
}

function enviar() {
    form.post(route('prestamos.store'))
}
</script>

<template>
    <Head title="Nuevo préstamo" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 720px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-journal-plus mr-2"></i>Nuevo préstamo</h3>
            <FlashMessage />

            <!-- Paso 1: buscar socio -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Paso 1 — Buscar socio</div>
                <div class="card-body">
                    <div class="input-group">
                        <input v-model="emailBusqueda" type="text" class="form-control"
                               placeholder="Ingresá el email del socio…" @input="buscarSocios">
                    </div>
                    <ul v-if="sociosEncontrados.length" class="list-group mt-2">
                        <button v-for="s in sociosEncontrados" :key="s.id" type="button"
                                class="list-group-item list-group-item-action"
                                @click="seleccionarSocio(s)">
                            {{ s.apellido }}, {{ s.nombre }} — {{ s.email }}
                        </button>
                    </ul>
                </div>
            </div>

            <!-- Paso 2: formulario -->
            <form v-if="form.socio_id" @submit.prevent="submit">
                <div class="alert alert-success">
                    Socio: <strong>{{ socioSeleccionado?.apellido }}, {{ socioSeleccionado?.nombre }}</strong>
                    <button type="button" class="btn btn-link btn-sm p-0 ml-2" @click="limpiarSocio">cambiar</button>
                </div>

                <div class="form-group">
                    <label>Material <span class="text-danger">*</span></label>
                    <select v-model.number="form.material_id" class="form-control"
                            :class="{ 'is-invalid': form.errors.material_id }">
                        <option value="">— Seleccioná un material —</option>
                        <option v-for="m in materiales" :key="m.id" :value="m.id">
                            {{ m.titulo }} (disp: {{ m.disponibilidad }}) — {{ m.codigo }}
                        </option>
                    </select>
                    <div class="invalid-feedback">{{ form.errors.material_id }}</div>
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
                    <button type="submit" class="btn btn-success" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm mr-1"></span>
                        Registrar préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({ areas: { type: Array, default: () => [] } })

const emailBusqueda      = ref('')
const sociosEncontrados  = ref([])
const socioSeleccionado  = ref(null)
const materiales         = ref([])

const hoy      = new Date().toISOString().split('T')[0]
const maxFecha = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0]

const form = useForm({
    socio_id:         '',
    material_id:      '',
    cantidad:         1,
    fecha_devolucion: hoy,
})

let buscarTimeout = null
async function buscarSocios() {
    clearTimeout(buscarTimeout)
    if (emailBusqueda.value.length < 3) { sociosEncontrados.value = []; return }
    buscarTimeout = setTimeout(async () => {
        const { data } = await axios.get(route('api.socios.buscar'), { params: { email: emailBusqueda.value } })
        sociosEncontrados.value = data
    }, 300)
}

async function seleccionarSocio(s) {
    socioSeleccionado.value = s
    form.socio_id           = s.id
    sociosEncontrados.value = []
    emailBusqueda.value     = s.email

    const { data } = await axios.get(route('api.materiales.disponibles'))
    materiales.value = data
}

function limpiarSocio() {
    form.socio_id        = ''
    socioSeleccionado.value = null
    emailBusqueda.value  = ''
    materiales.value     = []
}

function submit() {
    form.post(route('prestamos.store'))
}
</script>

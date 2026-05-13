<template>
    <Head title="Socios" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-people mr-2"></i>Panel de Socios</h3>
                <Link :href="route('usuarios.index')" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-plus mr-1"></i>Dar de alta desde Usuarios
                </Link>
            </div>

            <FlashMessage />

            <!-- Filtros -->
            <form @submit.prevent="buscar" class="mb-3">
                <div class="row g-2">
                    <div class="col-6 col-sm-4 col-md-3 mb-2">
                        <label for="filtro-socios" class="sr-only">Buscar por nombre, apellido o email</label>
                        <input id="filtro-socios" v-model="busqueda" type="text" class="form-control" placeholder="Nombre, apellido o email…">
                    </div>
                    <div class="col-6 col-sm-4 col-md-2 mb-2">
                        <select v-model="activo" class="form-control">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-4 col-sm-2 col-md-1 mb-2">
                        <input v-model="anio" type="number" min="1" max="6" class="form-control" placeholder="Año">
                    </div>
                    <div class="col-4 col-sm-2 col-md-1 mb-2">
                        <input v-model="division" type="number" min="1" max="6" class="form-control" placeholder="Div.">
                    </div>
                    <div class="col-4 col-sm-4 col-md-2 mb-2 d-flex align-items-center">
                        <div class="custom-control custom-checkbox">
                            <input id="moroso" v-model="moroso" type="checkbox" class="custom-control-input">
                            <label class="custom-control-label text-danger" for="moroso">Solo morosos</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 mb-2 d-flex">
                        <button type="submit" class="btn btn-outline-success mr-2" :disabled="cargando">
                            <span v-if="cargando" class="spinner-border spinner-border-sm mr-1"></span>
                            <i v-else class="bi bi-search mr-1"></i>
                            Buscar
                        </button>
                        <Link :href="route('socios.index')" class="btn btn-outline-secondary">Limpiar</Link>
                    </div>
                </div>
            </form>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="d-none d-md-table-cell">Email</th>
                            <th scope="col">Año / Div.</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="d-none d-lg-table-cell">Deudas</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="socio in socios.data" :key="socio.id">
                            <tr :class="{ 'table-active': expandido === socio.id }">
                                <td>{{ socio.apellido ? socio.apellido + ', ' + socio.nombre : socio.nombre }}</td>
                                <td class="d-none d-md-table-cell">{{ socio.email }}</td>
                                <td>{{ socio.anio ? socio.anio + '° ' + socio.division : '—' }}</td>
                                <td>
                                    <span :class="socio.activo ? 'badge badge-success' : 'badge badge-secondary'">
                                        {{ socio.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span v-if="socio.atrasados_count > 0" class="badge badge-danger">
                                        <i class="bi bi-exclamation-circle mr-1"></i>{{ socio.atrasados_count }} atrasado{{ socio.atrasados_count > 1 ? 's' : '' }}
                                    </span>
                                    <span v-else class="text-muted small">—</span>
                                </td>
                                <td class="text-right" style="white-space:nowrap">
                                    <button class="btn btn-sm btn-outline-info mr-1"
                                            :title="expandido === socio.id ? 'Cerrar detalle' : 'Ver detalle'"
                                            :aria-label="expandido === socio.id ? 'Cerrar detalle' : 'Ver detalle'"
                                            @click.stop="verDetalle(socio)">
                                        <i :class="expandido === socio.id ? 'bi bi-chevron-up' : 'bi bi-eye'"></i>
                                    </button>
                                    <Link :href="route('socios.edit', socio.id)" class="btn btn-sm btn-outline-primary" aria-label="Editar socio">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                </td>
                            </tr>

                            <!-- Panel de detalle expandible -->
                            <tr v-if="expandido === socio.id">
                                <td colspan="6" class="p-0 bg-light border-top-0">
                                    <div v-if="cargandoDetalle" class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-success mr-2"></div>
                                        Cargando historial…
                                    </div>
                                    <div v-else-if="detalle" class="p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-dark">
                                                <i class="bi bi-person-lines-fill mr-1"></i>
                                                {{ socio.apellido }}, {{ socio.nombre }}
                                            </strong>
                                            <Link :href="route('prestamos.create') + '?socio_id=' + socio.id"
                                                  class="btn btn-sm btn-success">
                                                <i class="bi bi-journal-plus mr-1"></i>Nuevo préstamo
                                            </Link>
                                        </div>

                                        <div class="row">
                                            <!-- Circulación activa -->
                                            <div class="col-12 col-md-7">
                                                <h6 class="text-muted mb-2">Circulación activa</h6>
                                                <div v-if="detalle.circulacion.length === 0" class="text-muted small mb-3">
                                                    Sin préstamos activos.
                                                </div>
                                                <table v-else class="table table-sm table-bordered mb-3">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Material</th>
                                                            <th>Vencimiento</th>
                                                            <th>Estado</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="p in detalle.circulacion" :key="p.id"
                                                            :class="p.estado === 'atrasado' ? 'table-danger' : ''">
                                                            <td>
                                                                <span class="d-block">{{ p.material }}</span>
                                                                <small class="text-muted">{{ p.codigo }}</small>
                                                            </td>
                                                            <td>{{ p.fecha_devolucion }}</td>
                                                            <td>
                                                                <span :class="claseEstadoPrestamo(p.estado)">{{ p.estado }}</span>
                                                            </td>
                                                            <td style="white-space:nowrap">
                        <div v-if="extendiendoId !== p.id">
                                <button class="btn btn-xs btn-outline-warning"
                                        @click="iniciarExtension(p)" aria-label="Extender préstamo">
                                    <i class="bi bi-calendar-plus"></i> Extender
                                </button>
                            </div>
                            <div v-else class="d-flex align-items-center">
                                <input v-model.number="diasExtension" type="number"
                                       min="1" max="30"
                                       class="form-control form-control-sm mr-1"
                                       style="width:65px"
                                       placeholder="días">
                                <button class="btn btn-xs btn-success mr-1"
                                        :disabled="procesandoExtension"
                                                                            @click="confirmarExtension(p, socio)">
                                                                        <span v-if="procesandoExtension" class="spinner-border spinner-border-sm"></span>
                                                                        <i v-else class="bi bi-check-lg"></i>
                                                                    </button>
                                                                    <button class="btn btn-xs btn-outline-secondary"
                                                                            @click="extendiendoId = null">
                                                                        <i class="bi bi-x-lg"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Historial + Deudas -->
                                            <div class="col-12 col-md-5">
                                                <div v-if="detalle.atrasados > 0" class="alert alert-danger py-2 mb-2">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    <strong>{{ detalle.atrasados }} préstamo{{ detalle.atrasados > 1 ? 's' : '' }} atrasado{{ detalle.atrasados > 1 ? 's' : '' }}</strong>
                                                </div>

                                                <h6 class="text-muted mb-2">Historial reciente</h6>
                                                <div v-if="detalle.historial.length === 0" class="text-muted small">
                                                    Sin devoluciones registradas.
                                                </div>
                                                <table v-else class="table table-sm table-bordered mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Material</th>
                                                            <th>Devuelto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="p in detalle.historial" :key="p.id">
                                                            <td><small>{{ p.material }}</small></td>
                                                            <td><small>{{ p.fecha_devolucion }}</small></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="!socios.data.length">
                            <td colspan="6" class="text-center text-muted">No se encontraron socios.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="socios.links" />
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    socios:  { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const busqueda = ref(props.filters.busqueda ?? '')
const activo   = ref(props.filters.activo ?? '')
const anio     = ref(props.filters.anio ?? '')
const division = ref(props.filters.division ?? '')
const moroso   = ref(!!props.filters.moroso)
const cargando = ref(false)

const expandido          = ref(null)
const detalle            = ref(null)
const cargandoDetalle    = ref(false)
const extendiendoId      = ref(null)
const diasExtension      = ref(7)
const procesandoExtension = ref(false)

function buscar() {
    cargando.value = true
    router.get(route('socios.index'), {
        busqueda: busqueda.value,
        activo:   activo.value,
        anio:     anio.value,
        division: division.value,
        moroso:   moroso.value ? 1 : '',
    }, { preserveState: true, onFinish: () => { cargando.value = false } })
}

async function verDetalle(socio) {
    if (expandido.value === socio.id) {
        expandido.value = null
        detalle.value   = null
        return
    }
    expandido.value      = socio.id
    detalle.value        = null
    cargandoDetalle.value = true
    extendiendoId.value  = null
    try {
        const { data } = await axios.get(route('api.socios.prestamos', socio.id))
        detalle.value = data
    } catch {
        alert('No se pudieron cargar los préstamos del socio.')
    } finally {
        cargandoDetalle.value = false
    }
}

async function recargarDetalle(socioId) {
    cargandoDetalle.value = true
    try {
        const { data } = await axios.get(route('api.socios.prestamos', socioId))
        detalle.value = data
    } catch {
        alert('No se pudieron recargar los préstamos del socio.')
    } finally {
        cargandoDetalle.value = false
    }
}

function iniciarExtension(prestamo) {
    extendiendoId.value = prestamo.id
    diasExtension.value = 7
}

async function confirmarExtension(prestamo, socio) {
    if (!diasExtension.value || diasExtension.value < 1) return
    procesandoExtension.value = true
    try {
        await axios.patch(route('prestamos.extender', prestamo.id), { dias: diasExtension.value }, {
            headers: { Accept: 'application/json' },
        })
        extendiendoId.value = null
        await recargarDetalle(socio.id)
    } catch (e) {
        const msg = e.response?.data?.errors?.prestamo?.[0] ?? 'No se pudo extender el préstamo.'
        alert(msg)
    } finally {
        procesandoExtension.value = false
    }
}

function claseEstadoPrestamo(estado) {
    return {
        activo:   'badge badge-success',
        pendiente:'badge badge-warning',
        atrasado: 'badge badge-danger',
        devuelto: 'badge badge-secondary',
    }[estado] ?? 'badge badge-light'
}
</script>

<style scoped>
.btn-xs {
    padding: .15rem .4rem;
    font-size: .75rem;
    line-height: 1.2;
    border-radius: .2rem;
}
</style>

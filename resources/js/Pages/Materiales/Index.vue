<template>
    <Head title="Materiales" />
    <AppNavbar />

    <div class="container-fluid page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-book mr-2"></i>Materiales</h3>
                <Link :href="route('materiales.create')" class="btn btn-success">
                    <i class="bi bi-book-plus mr-1"></i>Nuevo
                </Link>
            </div>

            <FlashMessage />

            <form @submit.prevent="buscar" class="form-inline mb-3">
                <input v-model="busqueda" type="text" class="form-control mr-2"
                       style="min-width:220px;" placeholder="Título, autor o código…">
                <select v-model="area_id" class="form-control mr-2">
                    <option value="">Todas las categorías</option>
                    <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
                <button type="submit" class="btn btn-outline-success mr-2">Buscar</button>
                <Link :href="route('materiales.index')" class="btn btn-outline-secondary">Limpiar</Link>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-sm" style="font-size:.82rem;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Año</th>
                            <th>Editorial</th>
                            <th>Clasificación Dewey</th>
                            <th>Categoría física</th>
                            <th>Ubic. física</th>
                            <th class="text-center">Disp.</th>
                            <th>Ejemplares</th>
                            <th>Tipo préstamo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in materiales.data" :key="m.id">
                            <td><code>{{ m.codigo }}</code></td>
                            <td>{{ m.titulo }}</td>
                            <td>{{ m.autor || '—' }}</td>
                            <td>{{ m.anio_publicacion || '—' }}</td>
                            <td>{{ m.editorial || '—' }}</td>
                            <td>
                                <span v-if="m.area">
                                    <code>{{ m.area.codigo_dewey }}</code>
                                    <span class="ml-1" style="font-size:.8rem;">{{ m.area.nombre }}</span>
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td>{{ m.categoria || '—' }}</td>
                            <td>
                                <code v-if="m.clasificacion_fisica">{{ m.clasificacion_fisica }}</code>
                                <span v-else>—</span>
                            </td>
                            <td class="text-center">
                                <span :class="m.disponibilidad > 0 ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ m.disponibilidad }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="verEjemplares(m)">
                                    <i class="bi bi-collection"></i> {{ m.disponibilidad }} / {{ m.total_ejemplares ?? m.disponibilidad }}
                                </button>
                            </td>
                            <td>
                                <span v-if="m.tipo_prestamo" class="badge badge-info">{{ m.tipo_prestamo }}</span>
                                <span v-else>—</span>
                            </td>
                            <td class="text-right" style="white-space:nowrap;">
                                <Link :href="route('materiales.edit', m.id)"
                                      class="btn btn-sm btn-outline-primary mr-1"
                                      title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                                <Link :href="route('materiales.qr', m.id)"
                                      class="btn btn-sm btn-outline-secondary"
                                      title="Ver QR">
                                    <i class="bi bi-qr-code"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!materiales.data.length">
                            <td colspan="11" class="text-center py-4">No se encontraron materiales.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="materiales.last_page > 1">
                <ul class="pagination justify-content-center">
                    <li v-for="link in materiales.links" :key="link.label"
                        :class="['page-item', { active: link.active, disabled: !link.url }]">
                        <Link v-if="link.url" :href="link.url" class="page-link">
                            <span v-if="link.label.includes('Anterior') || link.label.includes('Previous')">&laquo;</span>
                            <span v-else-if="link.label.includes('Siguiente') || link.label.includes('Next')">&raquo;</span>
                            <span v-else v-html="link.label"></span>
                        </Link>
                        <span v-else class="page-link">
                            <span v-if="link.label.includes('Anterior') || link.label.includes('Previous')">&laquo;</span>
                            <span v-else-if="link.label.includes('Siguiente') || link.label.includes('Next')">&raquo;</span>
                            <span v-else v-html="link.label"></span>
                        </span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal de ejemplares -->
    <div v-if="ejemplaresModal" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" @click.self="cerrarEjemplares">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ejemplares — {{ ejemplaresModal.titulo }}</h5>
                    <button type="button" class="close" @click="cerrarEjemplares">&times;</button>
                </div>
                <div class="modal-body">
                    <div v-if="ejemplaresLoading" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                    <table v-else class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Estado</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ej in ejemplaresModal.lista" :key="ej.id">
                                <td><code>{{ ej.codigo_ejemplar }}</code></td>
                                <td>
                                    <span :class="claseEstadoEjemplar(ej.estado)">{{ ej.estado }}</span>
                                    <span v-if="ej.prestamo_activo" class="ml-2 text-muted" style="font-size:.75rem;">
                                        → {{ ej.prestamo_activo.socio?.apellido }}, {{ ej.prestamo_activo.socio?.nombre }}
                                    </span>
                                </td>
                                <td style="max-width:120px;"><small class="text-muted">{{ ej.notas || '—' }}</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="cerrarEjemplares">Cerrar</button>
                </div>
            </div>
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

const props = defineProps({
    materiales: { type: Object, required: true },
    areas:      { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
})

const busqueda = ref(props.filters.busqueda ?? '')
const area_id  = ref(props.filters.area_id ?? '')

const ejemplaresModal    = ref(null)
const ejemplaresLoading  = ref(false)

function buscar() {
    router.get(route('materiales.index'), { busqueda: busqueda.value, area_id: area_id.value }, { preserveState: true })
}

async function verEjemplares(m) {
    ejemplaresModal.value    = { ...m, lista: [] }
    ejemplaresLoading.value  = true
    try {
        const { data } = await axios.get(route('materiales.ejemplares', m.id))
        ejemplaresModal.value.lista = data
    } finally {
        ejemplaresLoading.value = false
    }
}

function cerrarEjemplares() {
    ejemplaresModal.value = null
}

function claseEstadoEjemplar(estado) {
    return {
        disponible: 'badge badge-success',
        prestado:   'badge badge-warning',
        reservado:  'badge badge-info',
        baja:       'badge badge-secondary',
    }[estado] ?? 'badge badge-secondary'
}
</script>

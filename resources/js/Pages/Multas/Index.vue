<template>
    <Head title="Multas" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-currency-dollar mr-2"></i>Multas</h3>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <label for="filtro-multas" class="sr-only">Filtrar multas por estado</label>
                    <select id="filtro-multas" v-model="filtroPagada" class="form-control form-control-sm d-inline-block mr-2" style="width:auto" @change="filtrar">
                        <option value="">Todas</option>
                        <option value="0">Pendientes</option>
                        <option value="1">Pagadas</option>
                    </select>
                    <a :href="route('exportar.multas.csv')" class="btn btn-sm btn-outline-secondary mr-1" title="CSV">
                        <i class="bi bi-filetype-csv"></i>
                    </a>
                    <a :href="route('exportar.multas.pdf')" class="btn btn-sm btn-outline-secondary mr-1" title="PDF">
                        <i class="bi bi-filetype-pdf"></i>
                    </a>
                    <Link :href="route('multas.create')" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg mr-1"></i><span class="d-none d-md-inline">Nueva multa</span><span class="d-md-none">Nueva</span>
                    </Link>
                </div>
            </div>
            <FlashMessage />
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col" class="d-none d-md-table-cell">#</th>
                            <th scope="col">Socio</th>
                            <th scope="col">Monto</th>
                            <th scope="col" class="d-none d-sm-table-cell">Motivo</th>
                            <th scope="col" class="d-none d-lg-table-cell">Fecha</th>
                            <th scope="col">Estado</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in multas.data" :key="m.id" :class="{ 'table-success': m.pagada }">
                            <td class="d-none d-md-table-cell">{{ m.id }}</td>
                            <td>{{ m.socio?.apellido }}, {{ m.socio?.nombre }}</td>
                            <td><strong>${{ Number(m.monto).toFixed(2) }}</strong></td>
                            <td class="d-none d-sm-table-cell">{{ m.motivo }}</td>
                            <td class="d-none d-lg-table-cell">{{ m.fecha_creacion }}</td>
                            <td>
                                <span v-if="m.pagada" class="badge badge-success">Pagada</span>
                                <span v-else class="badge badge-danger">Pendiente</span>
                            </td>
                            <td class="text-right">
                                <button v-if="!m.pagada" class="btn btn-sm btn-outline-success mr-1" @click="confirmarPagar(m)" title="Marcar como pagada" aria-label="Marcar como pagada" :disabled="pagando === m.id">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button v-if="!m.pagada" class="btn btn-sm btn-outline-warning" @click="confirmarPerdon(m)" title="Perdonar" aria-label="Perdonar multa">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="multas.data.length === 0">
                            <td colspan="7" class="text-center text-muted py-4">No hay multas registradas</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination v-if="multas.links" :links="multas.links" />

            <!-- Modal pagar -->
            <div v-if="pagando" class="modal-backdrop d-block"></div>
            <div v-if="pagando" class="modal d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-pagar-title">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal-pagar-title">Confirmar pago</h5>
                            <button type="button" class="close" @click="pagando = null" aria-label="Cerrar">&times;</button>
                        </div>
                        <div class="modal-body">
                            ¿Marcar como pagada esta multa?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="pagando = null" :disabled="pagando">Cancelar</button>
                            <button type="button" class="btn btn-success" @click="ejecutarPago" :disabled="pagando">Confirmar pago</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal perdonar -->
            <div v-if="perdonando.activo" class="modal-backdrop d-block"></div>
            <div v-if="perdonando.activo" class="modal d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-perdonar-title">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal-perdonar-title">Perdonar multa</h5>
                            <button type="button" class="close" @click="perdonando = { activo: false, id: null }" aria-label="Cerrar">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>¿Perdonar esta multa?</p>
                            <div class="form-group">
                                <label>Observaciones (opcional)</label>
                                <textarea v-model="obsInput" class="form-control" rows="2" maxlength="500" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="perdonando = { activo: false, id: null }" :disabled="perdonando.activo && perdonando.id">Cancelar</button>
                            <button type="button" class="btn btn-warning" @click="ejecutarPerdon" :disabled="perdonando.activo && perdonando.id">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <AppFooter />
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({ multas: { type: Object, required: true }, filters: { type: Object } })

const filtroPagada = ref(props.filters?.pagada ?? '')
const pagando = ref(null)
const perdonando = ref({ activo: false, id: null })
const obsInput = ref('')

function filtrar() {
    router.get(route('multas.index'), { pagada: filtroPagada.value || '' }, { preserveState: true })
}

function confirmarPagar(multa) {
    pagando.value = multa.id
}

function ejecutarPago() {
    router.patch(route('multas.pagar', pagando.value), { preserveState: true, onSuccess: () => { pagando.value = null } })
}

function confirmarPerdon(multa) {
    perdonando.value = { activo: true, id: multa.id }
    obsInput.value = ''
}

function ejecutarPerdon() {
    router.patch(route('multas.perdonar', perdonando.value.id), {
        observaciones: obsInput.value || '',
        preserveState: true,
        onSuccess: () => { perdonando.value = { activo: false, id: null } },
    })
}
</script>

<template>
    <Head title="Alertas" />
    <AppNavbar />

    <div class="container page-content">
        <FlashMessage />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Registro de alertas</h4>
            <form v-if="alertas.data.length" @submit.prevent="marcarTodas">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-check2-all mr-1"></i> Marcar todas como leídas
                </button>
            </form>
        </div>

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-control form-control-sm" v-model="filtroTipo" @change="aplicarFiltros">
                    <option value="">Todos los tipos</option>
                    <option value="proximo_vencer">Próximo a vencer</option>
                    <option value="vencido">Vencido</option>
                    <option value="renovacion">Renovación</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-control form-control-sm" v-model="filtroLeida" @change="aplicarFiltros">
                    <option value="">Todas</option>
                    <option value="0">No leídas</option>
                    <option value="1">Leídas</option>
                </select>
            </div>
        </div>

        <div v-if="alertas.data.length === 0" class="alert alert-info">
            No hay alertas para mostrar.
        </div>

        <div v-else class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="alerta in alertas.data" :key="alerta.id"
                            :class="{ 'font-weight-bold': !alerta.leida }">
                            <td>
                                <span class="badge" :class="badgeClass(alerta.tipo)">
                                    {{ labelTipo(alerta.tipo) }}
                                </span>
                            </td>
                            <td>{{ alerta.descripcion }}</td>
                            <td class="text-nowrap">{{ alerta.fecha_alerta }}</td>
                            <td>
                                <span v-if="alerta.leida" class="text-muted small">Leída</span>
                                <span v-else class="badge badge-secondary">Nueva</span>
                            </td>
                            <td class="text-nowrap">
                                <!-- Marcar leída -->
                                <button v-if="!alerta.leida"
                                    @click="marcarLeida(alerta.id)"
                                    class="btn btn-sm btn-outline-success mr-1"
                                    title="Marcar como leída">
                                    <i class="bi bi-check"></i>
                                </button>

                                <!-- Ver préstamo -->
                                <Link v-if="alerta.prestamo_id"
                                    :href="route('prestamos.index')"
                                    class="btn btn-sm btn-outline-primary mr-1"
                                    title="Ver préstamos">
                                    <i class="bi bi-eye"></i>
                                </Link>

                                <!-- Dar de baja material (solo vencido/próximo) -->
                                <button v-if="!alerta.leida && alerta.tipo !== 'renovacion'"
                                    @click="abrirBaja(alerta)"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Dar de baja el material">
                                    <i class="bi bi-box-arrow-down"></i> Baja
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <nav v-if="alertas.last_page > 1" class="mt-3">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item" :class="{ disabled: !alertas.prev_page_url }">
                    <Link class="page-link" :href="alertas.prev_page_url ?? '#'">Anterior</Link>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">{{ alertas.current_page }} / {{ alertas.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: !alertas.next_page_url }">
                    <Link class="page-link" :href="alertas.next_page_url ?? '#'">Siguiente</Link>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Modal dar de baja -->
    <div v-if="modalBaja" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                        Dar de baja material
                    </h5>
                    <button type="button" class="close" @click="cerrarModal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Paso 1: Confirmar -->
                <template v-if="pasoModal === 1">
                    <div class="modal-body">
                        <p>¿Confirmás la baja de una unidad del material relacionado con esta alerta?</p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle mr-1"></i>
                            Esto descontará 1 unidad del stock disponible y quedará registrado como anotación.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
                        <button class="btn btn-danger" @click="pasoModal = 2">
                            Confirmar baja
                        </button>
                    </div>
                </template>

                <!-- Paso 2: Observaciones -->
                <template v-else>
                    <form @submit.prevent="confirmarBaja">
                        <div class="modal-body">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Motivo de la baja <span class="text-danger">*</span></label>
                                <textarea
                                    v-model="observaciones"
                                    class="form-control mt-1"
                                    rows="3"
                                    maxlength="500"
                                    placeholder="Ej: Material extraviado por el socio, material dañado..."
                                    autofocus
                                    required
                                ></textarea>
                                <small class="text-muted">{{ observaciones.length }}/500</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="pasoModal = 1">Atrás</button>
                            <button type="submit" class="btn btn-danger" :disabled="!observaciones.trim()">
                                <i class="bi bi-box-arrow-down mr-1"></i> Registrar baja
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    alertas: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const filtroTipo  = ref(props.filters.tipo ?? '')
const filtroLeida = ref(props.filters.leida ?? '')

// Modal
const modalBaja    = ref(false)
const pasoModal    = ref(1)
const observaciones = ref('')
const alertaActiva = ref(null)

function aplicarFiltros() {
    router.get(route('alertas.index'), {
        tipo:  filtroTipo.value  || undefined,
        leida: filtroLeida.value !== '' ? filtroLeida.value : undefined,
    }, { preserveState: true, replace: true })
}

function marcarLeida(id) {
    router.patch(route('alertas.leida', id))
}

function marcarTodas() {
    router.patch(route('alertas.todas-leidas'))
}

function abrirBaja(alerta) {
    alertaActiva.value = alerta
    pasoModal.value    = 1
    observaciones.value = ''
    modalBaja.value    = true
}

function cerrarModal() {
    modalBaja.value = false
    alertaActiva.value = null
    observaciones.value = ''
    pasoModal.value = 1
}

function confirmarBaja() {
    router.post(
        route('alertas.baja-material', alertaActiva.value.id),
        { observaciones: observaciones.value },
        { onSuccess: cerrarModal }
    )
}

function badgeClass(tipo) {
    return {
        'badge-warning': tipo === 'proximo_vencer',
        'badge-danger':  tipo === 'vencido',
        'badge-info':    tipo === 'renovacion',
    }
}

function labelTipo(tipo) {
    const labels = {
        proximo_vencer: 'Próximo a vencer',
        vencido:        'Vencido',
        renovacion:     'Renovación',
    }
    return labels[tipo] ?? tipo
}
</script>

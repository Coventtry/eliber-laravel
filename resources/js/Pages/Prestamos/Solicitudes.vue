<template>
    <Head title="Solicitudes pendientes" />
    <AppNavbar />

    <div class="container page-content">
        <FlashMessage />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                Solicitudes de reserva
                <span v-if="solicitudes.total > 0" class="badge badge-warning ml-2">{{ solicitudes.total }}</span>
            </h4>
        </div>

        <div v-if="solicitudes.data.length === 0" class="alert alert-info">
            No hay solicitudes pendientes.
        </div>

        <div v-else class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Solicitante</th>
                            <th scope="col">Material</th>
                            <th scope="col">Fecha</th>
                            <th scope="col" class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in solicitudes.data" :key="s.id">
                            <td class="text-truncate" style="max-width:150px;">{{ s.socio }}</td>
                            <td class="text-truncate" style="max-width:200px;">{{ s.material }}</td>
                            <td class="text-nowrap">{{ s.fecha }}</td>
                            <td class="text-right text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success mr-1"
                                    :disabled="procesando === s.id"
                                    @click="aprobar(s)"
                                >
                                    <i class="bi bi-check-lg"></i>
                                    <span class="d-none d-md-inline ml-1">Aprobar</span>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    :disabled="procesando === s.id"
                                    @click="abrirRechazo(s)"
                                >
                                    <i class="bi bi-x-lg"></i>
                                    <span class="d-none d-md-inline ml-1">Rechazar</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <nav v-if="solicitudes.last_page > 1" class="mt-3">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item" :class="{ disabled: !solicitudes.prev_page_url }">
                    <Link class="page-link" :href="solicitudes.prev_page_url ?? '#'">Anterior</Link>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">{{ solicitudes.current_page }} / {{ solicitudes.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: !solicitudes.next_page_url }">
                    <Link class="page-link" :href="solicitudes.next_page_url ?? '#'">Siguiente</Link>
                </li>
            </ul>
        </nav>
    </div>

    <div v-if="modalRechazo" class="modal d-block modal-backdrop-custom" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-rechazo-title">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="rechazar">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-rechazo-title">Rechazar solicitud</h5>
                        <button type="button" class="close" @click="modalRechazo = false" aria-label="Cerrar"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            <strong>{{ rechazo.socio }}</strong> — {{ rechazo.material }}
                        </p>
                        <div class="form-group mb-0">
                            <label>Motivo (opcional)</label>
                            <textarea
                                v-model="motivo"
                                class="form-control mt-1"
                                rows="2"
                                maxlength="500"
                                placeholder="Ej: Material no disponible en este momento..."
                            ></textarea>
                            <small class="text-muted">{{ motivo.length }}/500</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modalRechazo = false">Cancelar</button>
                        <button type="submit" class="btn btn-danger" :disabled="procesando === rechazo.id">
                            <span v-if="procesando === rechazo.id" class="spinner-border spinner-border-sm mr-1"></span>
                            <i v-else class="bi bi-x-lg mr-1"></i>
                            Rechazar
                        </button>
                    </div>
                </form>
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

defineProps({
    solicitudes: { type: Object, required: true },
})

const procesando = ref(null)
const modalRechazo = ref(false)
const rechazo = ref({ id: null, socio: '', material: '' })
const motivo = ref('')

function aprobar(s) {
    if (!confirm('¿Aprobar esta solicitud? Se creará un préstamo automáticamente.')) return
    procesando.value = s.id
    router.patch(route('prestamos.solicitudes.aprobar', s.id), {}, {
        onFinish: () => { procesando.value = null },
    })
}

function abrirRechazo(s) {
    rechazo.value = { id: s.id, socio: s.socio, material: s.material }
    motivo.value = ''
    modalRechazo.value = true
}

function rechazar() {
    procesando.value = rechazo.value.id
    modalRechazo.value = false
    router.patch(route('prestamos.solicitudes.rechazar', rechazo.value.id), {
        motivo: motivo.value || undefined,
    }, {
        onFinish: () => { procesando.value = null },
    })
}
</script>

<style scoped>
.modal-backdrop-custom {
    background: rgba(0,0,0,.5);
}
</style>

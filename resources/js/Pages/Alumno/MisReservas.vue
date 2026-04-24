<template>
    <Head title="Mis reservas" />
    <AppNavbarAlumno />

    <div class="container page-content">
        <FlashMessage />

        <h4 class="mb-4">Mis reservas</h4>

        <div v-if="!tiene_socio" class="alert alert-warning">
            <i class="bi bi-info-circle mr-2"></i>
            Tu usuario no está vinculado a un registro de socio. Contactá al bibliotecario.
        </div>

        <template v-else>
            <div v-if="reservas.data.length === 0" class="alert alert-info">
                No tenés reservas registradas.
            </div>

            <div v-else class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Material</th>
                                <th>Estado</th>
                                <th>Fecha reserva</th>
                                <th>Vencimiento</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in reservas.data" :key="r.id">
                                <td>{{ r.material }}</td>
                                <td>
                                    <span class="badge" :class="badgeEstado(r.estado)">{{ labelEstado(r.estado) }}</span>
                                </td>
                                <td class="text-nowrap">{{ r.fecha_reserva }}</td>
                                <td class="text-nowrap">{{ r.fecha_vencimiento ?? '—' }}</td>
                                <td class="text-right">
                                    <button
                                        v-if="r.estado === 'pendiente' || r.estado === 'aprobada'"
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        :disabled="cancelando === r.id"
                                        @click="cancelar(r)"
                                    >
                                        {{ cancelando === r.id ? '...' : 'Cancelar' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <nav v-if="reservas.last_page > 1" class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item" :class="{ disabled: !reservas.prev_page_url }">
                        <Link class="page-link" :href="reservas.prev_page_url ?? '#'">Anterior</Link>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">{{ reservas.current_page }} / {{ reservas.last_page }}</span>
                    </li>
                    <li class="page-item" :class="{ disabled: !reservas.next_page_url }">
                        <Link class="page-link" :href="reservas.next_page_url ?? '#'">Siguiente</Link>
                    </li>
                </ul>
            </nav>
        </template>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppNavbarAlumno from '@/Components/AppNavbarAlumno.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    reservas:    { type: Object, default: () => ({ data: [] }) },
    tiene_socio: { type: Boolean, default: false },
})

const cancelando = ref(null)

function cancelar(reserva) {
    if (!confirm('¿Cancelar esta reserva?')) return
    cancelando.value = reserva.id
    router.delete(route('alumno.reservas.cancel', reserva.id), {
        onFinish: () => { cancelando.value = null },
    })
}

function badgeEstado(estado) {
    return {
        'badge-warning':   estado === 'pendiente',
        'badge-success':   estado === 'aprobada',
        'badge-danger':    estado === 'rechazada' || estado === 'cancelada',
        'badge-secondary': estado === 'expirada',
    }
}

function labelEstado(estado) {
    const map = {
        pendiente: 'Pendiente',
        aprobada:  'Aprobada',
        rechazada: 'Rechazada',
        cancelada: 'Cancelada',
        expirada:  'Expirada',
    }
    return map[estado] ?? estado
}
</script>

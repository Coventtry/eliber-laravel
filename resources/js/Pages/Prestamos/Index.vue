<template>
    <Head title="Préstamos" />
    <AppNavbar />

    <div class="container-fluid page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-journal-bookmark mr-2"></i>Préstamos</h3>
                <Link :href="route('prestamos.create')" class="btn btn-success">
                    <i class="bi bi-journal-plus mr-1"></i>Nuevo
                </Link>
            </div>

            <FlashMessage />

            <!-- Filtros de estado -->
            <div class="mb-3 d-flex flex-wrap gap-2">
                <Link v-for="e in estados" :key="e.value"
                      :href="route('prestamos.index', e.value ? { estado: e.value } : {})"
                      :class="['btn btn-sm', filtroActivo === e.value ? 'btn-dark' : 'btn-outline-dark']">
                    {{ e.label }}
                </Link>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Socio</th>
                            <th scope="col">Material</th>
                            <th scope="col" class="d-none d-md-table-cell">Préstamo</th>
                            <th scope="col" class="d-none d-md-table-cell">Devolución</th>
                            <th scope="col">Estado</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in prestamos.data" :key="p.id">
                            <td>{{ p.socio?.apellido }}, {{ p.socio?.nombre }}</td>
                            <td>{{ p.material?.titulo }}</td>
                            <td class="d-none d-md-table-cell">{{ formatearFecha(p.fecha_prestamo) }}</td>
                            <td :class="[{ 'text-danger font-weight-bold': p.estado === 'atrasado' }, 'd-none d-md-table-cell']">
                                {{ formatearFecha(p.fecha_devolucion) }}
                            </td>
                            <td>
                                <span :class="`badge badge-${claseEstado(p.estado)}`">{{ p.estado }}</span>
                            </td>
                            <td class="text-right">
                                <template v-if="p.estado !== 'devuelto'">
                                    <Link :href="route('prestamos.devolucion', p.id)"
                                          class="btn btn-sm btn-outline-success mr-1"
                                          aria-label="Registrar devolución">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </Link>
                                </template>
                                <a v-if="p.socio?.telefono" :href="enlaceWhatsapp(p)" target="_blank"
                                   class="btn btn-sm btn-outline-success" aria-label="Enviar WhatsApp al socio">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </td>
                        </tr>
                        <tr v-if="!prestamos.data.length">
                            <td colspan="6" class="text-center text-muted">No hay préstamos.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="prestamos.links" />
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    prestamos: { type: Object, required: true },
    filters:   { type: Object, default: () => ({}) },
})

const filtroActivo = computed(() => props.filters.estado ?? '')

const estados = [
    { label: 'Todos',     value: '' },
    { label: 'Activos',   value: 'activo' },
    { label: 'Pendiente', value: 'pendiente' },
    { label: 'Atrasados', value: 'atrasado' },
    { label: 'Devueltos', value: 'devuelto' },
]

function claseEstado(estado) {
    return { activo: 'info', pendiente: 'warning', atrasado: 'danger', devuelto: 'success' }[estado] ?? 'secondary'
}

function formatearFecha(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('es-AR')
}

function enlaceWhatsapp(p) {
    const telefono = (p.socio?.telefono ?? '').replace(/\D/g, '')
    const mensaje = encodeURIComponent(`Hola ${p.socio?.nombre}, recordamos que el préstamo de *${p.material?.titulo}* vence el ${formatearFecha(p.fecha_devolucion)}.`)
    return `https://wa.me/54${telefono}?text=${mensaje}`
}
</script>

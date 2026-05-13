<template>
    <Head title="Mis préstamos" />
    <AppNavbarAlumno />

    <div class="container page-content">
        <FlashMessage />

        <h4 class="mb-4">Mis préstamos</h4>

        <div v-if="!tiene_socio" class="alert alert-warning">
            <i class="bi bi-info-circle mr-2"></i>
            Tu usuario no está vinculado a un registro de socio. Contactá al bibliotecario.
        </div>

        <template v-else>
            <h5 class="mb-3">
                <i class="bi bi-play-circle mr-1"></i>Préstamos activos
            </h5>

            <div v-if="activos.length === 0" class="alert alert-info mb-4">
                No tenés préstamos activos.
            </div>

            <div v-else class="card shadow-sm mb-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Material</th>
                                <th>Estado</th>
                                <th>Inicio</th>
                                <th>Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in activos" :key="p.id">
                                <td>{{ p.material }}</td>
                                <td>
                                    <span class="badge" :class="badgeEstado(p.estado)">{{ labelEstado(p.estado) }}</span>
                                </td>
                                <td class="text-nowrap">{{ p.fecha_inicio }}</td>
                                <td class="text-nowrap">{{ p.fecha_venc }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <h5 class="mb-3">
                <i class="bi bi-clock-history mr-1"></i>Historial
            </h5>

            <div v-if="historial.data.length === 0" class="alert alert-info">
                No tenés préstamos en el historial.
            </div>

            <div v-else class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Material</th>
                                <th>Estado</th>
                                <th>Inicio</th>
                                <th>Devolución</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in historial.data" :key="p.id">
                                <td>{{ p.material }}</td>
                                <td>
                                    <span class="badge" :class="badgeEstado(p.estado)">{{ labelEstado(p.estado) }}</span>
                                </td>
                                <td class="text-nowrap">{{ p.fecha_inicio }}</td>
                                <td class="text-nowrap">{{ p.fecha_venc }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <nav v-if="historial.last_page > 1" class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item" :class="{ disabled: !historial.prev_page_url }">
                        <Link class="page-link" :href="historial.prev_page_url ?? '#'">Anterior</Link>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">{{ historial.current_page }} / {{ historial.last_page }}</span>
                    </li>
                    <li class="page-item" :class="{ disabled: !historial.next_page_url }">
                        <Link class="page-link" :href="historial.next_page_url ?? '#'">Siguiente</Link>
                    </li>
                </ul>
            </nav>
        </template>
    </div>

    <AppFooter />
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppNavbarAlumno from '@/Components/AppNavbarAlumno.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    activos:    { type: Array, default: () => [] },
    historial:  { type: Object, default: () => ({ data: [] }) },
    tiene_socio: { type: Boolean, default: false },
})

function badgeEstado(estado) {
    return {
        'badge-primary':  estado === 'activo',
        'badge-danger':   estado === 'atrasado',
        'badge-secondary': estado === 'devuelto',
    }
}

function labelEstado(estado) {
    const map = {
        activo:   'Activo',
        atrasado: 'Atrasado',
        devuelto: 'Devuelto',
    }
    return map[estado] ?? estado
}
</script>

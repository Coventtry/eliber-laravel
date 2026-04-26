<template>
    <Head title="Dashboard Admin" />

    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Overview</h4>
                <small class="text-muted">Panel de administración — E-liber</small>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="row mb-4">
            <div v-for="card in statCards" :key="card.label" class="col-sm-6 col-xl-3 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon mr-3" :class="card.color">
                            <i :class="['bi', card.icon]"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ card.value }}</div>
                            <div class="stat-label">{{ card.label }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Reservas pendientes -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center py-2">
                        <span class="font-weight-bold">Reservas pendientes</span>
                        <Link :href="route('prestamos.index')" class="btn btn-sm btn-outline-primary">Ver todas</Link>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="reservas_pendientes.length === 0" class="p-3 text-muted small">
                            No hay reservas pendientes.
                        </div>
                        <table v-else class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Socio</th>
                                    <th>Material</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in reservas_pendientes" :key="r.id">
                                    <td class="text-truncate" style="max-width:120px;">{{ r.socio }}</td>
                                    <td class="text-truncate" style="max-width:150px;">{{ r.material }}</td>
                                    <td class="text-nowrap small text-muted">{{ r.fecha }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Préstamos próximos a vencer -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center py-2">
                        <span class="font-weight-bold">Próximos vencimientos <span class="badge badge-warning ml-1">{{ diasAlerta }} días</span></span>
                        <Link :href="route('prestamos.index')" class="btn btn-sm btn-outline-warning">Ver todos</Link>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="proximos_vencer.length === 0" class="p-3 text-muted small">
                            No hay préstamos próximos a vencer.
                        </div>
                        <table v-else class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Socio</th>
                                    <th>Material</th>
                                    <th>Vence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in proximos_vencer" :key="p.id">
                                    <td class="text-truncate" style="max-width:120px;">{{ p.socio }}</td>
                                    <td class="text-truncate" style="max-width:150px;">{{ p.material }}</td>
                                    <td class="text-nowrap small text-warning font-weight-bold">{{ p.fecha_devolucion }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default { layout: AdminLayout }
</script>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    stats:               { type: Object, required: true },
    reservas_pendientes: { type: Array,  default: () => [] },
    proximos_vencer:     { type: Array,  default: () => [] },
    diasAlerta:          { type: Number, default: 4 },
})

const statCards = computed(() => [
    { label: 'Socios activos',    value: props.stats.socios_activos,   icon: 'bi-person-check', color: 'stat-blue'   },
    { label: 'Préstamos del mes', value: props.stats.prestamos_mes,    icon: 'bi-journal-arrow-up', color: 'stat-green'  },
    { label: 'Unidades en stock', value: props.stats.materiales_total, icon: 'bi-book',          color: 'stat-purple' },
    { label: 'Alertas sin leer',  value: props.stats.alertas_sin_leer, icon: 'bi-bell',          color: 'stat-red'    },
])
</script>

<style scoped>
.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.stat-blue   { background: #dbeafe; color: #1d4ed8; }
.stat-green  { background: #d1fae5; color: #065f46; }
.stat-purple { background: #ede9fe; color: #6d28d9; }
.stat-red    { background: #fee2e2; color: #991b1b; }

.stat-value  { font-size: 1.6rem; font-weight: 700; line-height: 1; }
.stat-label  { font-size: .78rem; color: #6c757d; margin-top: 2px; }

/* Dark mode */
:global([data-theme="dark"]) .stat-blue   { background: #1e3a5f; color: #93c5fd; }
:global([data-theme="dark"]) .stat-green  { background: #14532d; color: #6ee7b7; }
:global([data-theme="dark"]) .stat-purple { background: #3b0764; color: #c4b5fd; }
:global([data-theme="dark"]) .stat-red    { background: #450a0a; color: #fca5a5; }
:global([data-theme="dark"]) .stat-label  { color: #94a3b8; }
:global([data-theme="dark"]) .stat-value  { color: #f1f5f9; }
</style>

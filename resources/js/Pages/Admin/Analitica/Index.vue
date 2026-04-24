<template>
    <Head title="Analítica" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Analítica</h4>
            <p class="text-muted small mb-0">Métricas y estadísticas del sistema</p>
        </div>
    </div>

    <!-- Tarjetas de totales -->
    <div class="row mb-4">
        <div class="col-6 col-md-4 col-lg mb-3" v-for="card in statCards" :key="card.label">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3 d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                         :style="`width:42px;height:42px;background:${card.bg};`">
                        <i :class="['bi', card.icon]" :style="`color:${card.color};font-size:1.1rem;`"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold" style="font-size:1.25rem;line-height:1.1;">{{ card.value }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ card.label }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos principales -->
    <div class="row mb-4">
        <!-- Préstamos por mes -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Préstamos por mes (últimos 6 meses)</h6>
                    <div style="position:relative;height:260px;">
                        <Bar v-if="barData" :data="barData" :options="barOptions" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Socios activos vs baja -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="font-weight-bold mb-3">Socios: activos vs dados de baja</h6>
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="min-height:220px;">
                        <div style="position:relative;width:100%;max-width:240px;">
                            <Doughnut v-if="doughnutData" :data="doughnutData" :options="doughnutOptions" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mt-2" style="gap:1.2rem;">
                        <div class="d-flex align-items-center" style="gap:.35rem;">
                            <div style="width:12px;height:12px;border-radius:3px;background:#1a3a5c;"></div>
                            <span class="small text-muted">Activos ({{ sociosActivos }})</span>
                        </div>
                        <div class="d-flex align-items-center" style="gap:.35rem;">
                            <div style="width:12px;height:12px;border-radius:3px;background:#dee2e6;"></div>
                            <span class="small text-muted">Dados de baja ({{ sociosBaja }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Distribución por área -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Materiales por área</h6>
                    <div v-if="porArea.length" style="position:relative;height:260px;">
                        <Bar :data="areaData" :options="areaOptions" />
                    </div>
                    <div v-else class="text-center text-muted py-5">Sin datos</div>
                </div>
            </div>
        </div>

        <!-- Top 5 materiales -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Top 5 materiales más prestados</h6>
                    <div v-if="topMateriales.length">
                        <div
                            v-for="(mat, idx) in topMateriales"
                            :key="idx"
                            class="d-flex align-items-center mb-3"
                        >
                            <span class="font-weight-bold mr-2 flex-shrink-0"
                                  style="width:22px;height:22px;background:var(--eliber-primary,#1a3a5c);color:#fff;border-radius:50%;font-size:.72rem;display:flex;align-items:center;justify-content:center;">
                                {{ idx + 1 }}
                            </span>
                            <div class="flex-grow-1 mr-2 overflow-hidden">
                                <div class="font-weight-bold text-truncate" style="font-size:.82rem;">{{ mat.titulo }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ mat.autor }}</div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge badge-primary" style="font-size:.75rem;">
                                    {{ mat.total_prestamos }} préstamo{{ mat.total_prestamos !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        <!-- Barra de progreso relativa -->
                        <div v-for="(mat, idx) in topMateriales" :key="'bar-'+idx" class="mb-1" style="display:none;">
                        </div>
                    </div>
                    <div v-else class="text-center text-muted py-5">Sin datos de préstamos</div>
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
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    BarElement, ArcElement,
    Title, Tooltip, Legend,
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend)

const props = defineProps({
    prestamos_por_mes: { type: Array, default: () => [] },
    por_area:          { type: Array, default: () => [] },
    socios_activos:    { type: Number, default: 0 },
    socios_baja:       { type: Number, default: 0 },
    top_materiales:    { type: Array, default: () => [] },
    totales:           { type: Object, default: () => ({}) },
})

const sociosActivos = computed(() => props.socios_activos)
const sociosBaja    = computed(() => props.socios_baja)
const porArea       = computed(() => props.por_area)
const topMateriales = computed(() => props.top_materiales)

// ── Stat cards ────────────────────────────────────────────────────────────────
const statCards = computed(() => [
    { label: 'Préstamos totales',   value: props.totales.prestamos_total   ?? 0, icon: 'bi-book',          color: '#1a3a5c', bg: '#e8f0fe' },
    { label: 'Préstamos activos',   value: props.totales.prestamos_activos ?? 0, icon: 'bi-bookmark-check', color: '#1e7e34', bg: '#d4edda' },
    { label: 'Préstamos vencidos',  value: props.totales.prestamos_vencidos ?? 0, icon: 'bi-exclamation-circle', color: '#c0392b', bg: '#fde8e8' },
    { label: 'Materiales',          value: props.totales.materiales_total   ?? 0, icon: 'bi-journal-text',  color: '#6f42c1', bg: '#f0e6ff' },
    { label: 'Socios',              value: props.totales.socios_total       ?? 0, icon: 'bi-people',        color: '#e67e22', bg: '#fef3e2' },
])

// ── Gráfico: Préstamos por mes ────────────────────────────────────────────────
const barData = computed(() => ({
    labels:   props.prestamos_por_mes.map(d => d.label),
    datasets: [{
        label:           'Préstamos',
        data:            props.prestamos_por_mes.map(d => d.value),
        backgroundColor: '#1a3a5c',
        borderRadius:    4,
        borderSkipped:   false,
    }],
}))

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f0f0f0' } },
        x: { grid: { display: false } },
    },
}

// ── Gráfico: Donut socios ─────────────────────────────────────────────────────
const doughnutData = computed(() => ({
    labels:   ['Activos', 'Dados de baja'],
    datasets: [{
        data:            [props.socios_activos, props.socios_baja],
        backgroundColor: ['#1a3a5c', '#dee2e6'],
        borderWidth:     0,
    }],
}))

const doughnutOptions = {
    responsive:          true,
    maintainAspectRatio: true,
    cutout:              '68%',
    plugins: { legend: { display: false } },
}

// ── Gráfico: Materiales por área ──────────────────────────────────────────────
const areaColors = [
    '#1a3a5c','#e8a020','#6f42c1','#20c997','#dc3545',
    '#fd7e14','#0dcaf0','#198754','#6610f2','#d63384',
]

const areaData = computed(() => ({
    labels:   props.por_area.map(d => d.label),
    datasets: [{
        label:           'Materiales',
        data:            props.por_area.map(d => d.value),
        backgroundColor: props.por_area.map((_, i) => areaColors[i % areaColors.length]),
        borderRadius:    4,
        borderSkipped:   false,
    }],
}))

const areaOptions = {
    responsive:          true,
    maintainAspectRatio: false,
    indexAxis:           'y',
    plugins: { legend: { display: false } },
    scales: {
        x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f0f0f0' } },
        y: { grid: { display: false } },
    },
}
</script>

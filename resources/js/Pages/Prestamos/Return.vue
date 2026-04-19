<template>
    <Head title="Devolución" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 600px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-arrow-return-left mr-2"></i>Devolución de préstamo</h3>
            <FlashMessage />

            <dl class="row">
                <dt class="col-sm-4">Socio</dt>
                <dd class="col-sm-8">{{ prestamo.socio?.apellido }}, {{ prestamo.socio?.nombre }}</dd>
                <dt class="col-sm-4">Material</dt>
                <dd class="col-sm-8">{{ prestamo.material?.titulo }}</dd>
                <dt class="col-sm-4">Vencimiento</dt>
                <dd class="col-sm-8" :class="{ 'text-danger': prestamo.estado === 'atrasado' }">
                    {{ prestamo.fecha_devolucion }}
                </dd>
                <dt class="col-sm-4">Estado</dt>
                <dd class="col-sm-8">
                    <span :class="`badge badge-${estadoClass}`">{{ prestamo.estado }}</span>
                </dd>
            </dl>

            <hr>

            <div class="d-flex gap-3 flex-wrap">
                <!-- Devolver -->
                <form @submit.prevent="devolver" class="mr-2">
                    <button type="submit" class="btn btn-success" :disabled="prestamo.estado === 'devuelto'">
                        <i class="bi bi-check-circle mr-1"></i>Registrar devolución
                    </button>
                </form>

                <!-- Extender -->
                <form @submit.prevent="extender" class="d-flex align-items-center">
                    <input v-model.number="dias" type="number" min="1" max="30"
                           class="form-control form-control-sm mr-2" style="width: 80px;" placeholder="Días">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar-plus mr-1"></i>Extender
                    </button>
                </form>
            </div>

            <div class="mt-3">
                <Link :href="route('prestamos.index')" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left mr-1"></i>Volver al listado
                </Link>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({ prestamo: { type: Object, required: true } })
const dias  = ref(7)

const estadoClass = computed(() =>
    ({ activo: 'info', pendiente: 'warning', atrasado: 'danger', devuelto: 'success' }[props.prestamo.estado] ?? 'secondary')
)

function devolver() {
    router.patch(route('prestamos.devolver', props.prestamo.id))
}

function extender() {
    router.patch(route('prestamos.extender', props.prestamo.id), { dias: dias.value })
}
</script>

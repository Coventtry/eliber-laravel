<template>
    <Head title="Catálogo" />
    <AppNavbarAlumno />

    <div class="container page-content">
        <FlashMessage />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Catálogo de materiales disponibles</h4>
            <span class="text-muted small">{{ materiales.total }} material{{ materiales.total !== 1 ? 'es' : '' }}</span>
        </div>

        <!-- Filtros -->
        <form @submit.prevent="buscar" class="row mb-4" style="gap:.5rem 0;">
            <div class="col-sm-6 col-md-5">
                <input
                    v-model="form.search"
                    type="text"
                    class="form-control"
                    placeholder="Buscar por título o autor..."
                >
            </div>
            <div class="col-sm-4 col-md-3">
                <select v-model="form.area_id" class="form-control">
                    <option value="">Todas las áreas</option>
                    <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search mr-1"></i>Buscar
                </button>
                <Link v-if="hayFiltros" :href="route('alumno.catalogo')" class="btn btn-outline-secondary ml-1">
                    Limpiar
                </Link>
            </div>
        </form>

        <div v-if="materiales.data.length === 0" class="alert alert-info">
            No hay materiales disponibles para los filtros seleccionados.
        </div>

        <div v-else class="row">
            <div v-for="m in materiales.data" :key="m.id" class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-1">{{ m.titulo }}</h6>
                        <p class="text-muted small mb-1">{{ m.autor }}</p>
                        <p class="text-muted small mb-0">
                            <span v-if="m.area" class="badge badge-light border mr-1">{{ m.area }}</span>
                            <span v-if="m.anio">{{ m.anio }}</span>
                        </p>
                    </div>
                    <div class="card-footer py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ m.categoria }}</small>
                            <span class="badge badge-success">{{ m.disponibilidad }} disp.</span>
                        </div>
                        <button
                            v-if="tieneSocio"
                            type="button"
                            class="btn btn-primary btn-sm btn-block mt-2"
                            :disabled="reservando === m.id"
                            @click="reservar(m)"
                        >
                            <i class="bi bi-bookmark-plus mr-1"></i>
                            {{ reservando === m.id ? 'Reservando...' : 'Reservar' }}
                        </button>
                        <p v-else class="text-muted small text-center mt-2 mb-0">
                            <i class="bi bi-info-circle mr-1"></i>Sin socio vinculado
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <nav v-if="materiales.last_page > 1" class="mt-2">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item" :class="{ disabled: !materiales.prev_page_url }">
                    <Link class="page-link" :href="materiales.prev_page_url ?? '#'">Anterior</Link>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">{{ materiales.current_page }} / {{ materiales.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: !materiales.next_page_url }">
                    <Link class="page-link" :href="materiales.next_page_url ?? '#'">Siguiente</Link>
                </li>
            </ul>
        </nav>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppNavbarAlumno from '@/Components/AppNavbarAlumno.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    materiales:  { type: Object,  required: true },
    areas:       { type: Array,   default: () => [] },
    filters:     { type: Object,  default: () => ({}) },
    tieneSocio:  { type: Boolean, default: false },
})

const form = ref({
    search:  props.filters.search  ?? '',
    area_id: props.filters.area_id ?? '',
})

const hayFiltros = computed(() => form.value.search || form.value.area_id)

const reservando = ref(null)

function buscar() {
    router.get(route('alumno.catalogo'), {
        search:  form.value.search  || undefined,
        area_id: form.value.area_id || undefined,
    }, { preserveState: true, replace: true })
}

function reservar(material) {
    reservando.value = material.id
    router.post(route('alumno.reservas.store'), { material_id: material.id }, {
        onFinish: () => { reservando.value = null },
    })
}
</script>

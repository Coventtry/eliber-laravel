<template>
    <Head title="Materiales" />
    <AppNavbar />

    <div class="container-fluid page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-book mr-2"></i>Materiales</h3>
                <Link :href="route('materiales.create')" class="btn btn-success">
                    <i class="bi bi-book-plus mr-1"></i>Nuevo
                </Link>
            </div>

            <FlashMessage />

            <form @submit.prevent="buscar" class="mb-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-4 col-md-5 mb-2">
                        <label for="filtro-materiales" class="sr-only">Buscar por título o autor</label>
                        <input id="filtro-materiales" v-model="busqueda" type="text" class="form-control" placeholder="Título o autor…">
                    </div>
                    <div class="col-12 col-sm-4 col-md-3 mb-2">
                        <select v-model="area_id" class="form-control">
                            <option value="">Todas las áreas</option>
                            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-md-4 mb-2 d-flex">
                        <button type="submit" class="btn btn-outline-success mr-2" :disabled="cargando">
                            <span v-if="cargando" class="spinner-border spinner-border-sm mr-1"></span>
                            <i v-else class="bi bi-search mr-1"></i>
                            Buscar
                        </button>
                        <Link :href="route('materiales.index')" class="btn btn-outline-secondary">Limpiar</Link>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Título</th>
                            <th scope="col" class="d-none d-md-table-cell">Autor</th>
                            <th scope="col" class="d-none d-lg-table-cell">Área</th>
                            <th scope="col">Disp.</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in materiales.data" :key="m.id">
                            <td><code>{{ m.codigo }}</code></td>
                            <td>{{ m.titulo }}</td>
                            <td class="d-none d-md-table-cell">{{ m.autor }}</td>
                            <td class="d-none d-lg-table-cell"><small>{{ m.area?.nombre }}</small></td>
                            <td>
                                <span :class="m.disponibilidad > 0 ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ m.disponibilidad }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('materiales.edit', m.id)" class="btn btn-sm btn-outline-primary mr-1"
                                      aria-label="Editar material">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                                <Link :href="route('materiales.qr', m.id)" class="btn btn-sm btn-outline-secondary"
                                      aria-label="Ver código QR">
                                    <i class="bi bi-qr-code"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!materiales.data.length">
                            <td colspan="6" class="text-center text-muted">No se encontraron materiales.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="materiales.links" />
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    materiales: { type: Object, required: true },
    areas:      { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
})

const busqueda  = ref(props.filters.busqueda ?? '')
const area_id   = ref(props.filters.area_id ?? '')
const cargando  = ref(false)

function buscar() {
    cargando.value = true
    router.get(route('materiales.index'), { busqueda: busqueda.value, area_id: area_id.value }, {
        preserveState: true,
        onFinish: () => { cargando.value = false },
    })
}
</script>

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

            <form @submit.prevent="buscar" class="form-inline mb-3">
                <input v-model="busqueda" type="text" class="form-control mr-2" placeholder="Título o autor…">
                <select v-model="area_id" class="form-control mr-2">
                    <option value="">Todas las áreas</option>
                    <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
                <button type="submit" class="btn btn-outline-success mr-2">Buscar</button>
                <Link :href="route('materiales.index')" class="btn btn-outline-secondary">Limpiar</Link>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Área</th>
                            <th>Disp.</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in materiales.data" :key="m.id">
                            <td><code>{{ m.codigo }}</code></td>
                            <td>{{ m.titulo }}</td>
                            <td>{{ m.autor }}</td>
                            <td><small>{{ m.area?.nombre }}</small></td>
                            <td>
                                <span :class="m.disponibilidad > 0 ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ m.disponibilidad }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('materiales.edit', m.id)" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                                <Link :href="route('materiales.qr', m.id)" class="btn btn-sm btn-outline-secondary">
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

            <nav v-if="materiales.last_page > 1">
                <ul class="pagination justify-content-center">
                    <li v-for="link in materiales.links" :key="link.label"
                        :class="['page-item', { active: link.active, disabled: !link.url }]">
                        <Link v-if="link.url" :href="link.url" class="page-link">
                            <span v-if="link.label.includes('Anterior') || link.label.includes('Previous')">&laquo;</span>
                            <span v-else-if="link.label.includes('Siguiente') || link.label.includes('Next')">&raquo;</span>
                            <span v-else v-html="link.label"></span>
                        </Link>
                        <span v-else class="page-link">
                            <span v-if="link.label.includes('Anterior') || link.label.includes('Previous')">&laquo;</span>
                            <span v-else-if="link.label.includes('Siguiente') || link.label.includes('Next')">&raquo;</span>
                            <span v-else v-html="link.label"></span>
                        </span>
                    </li>
                </ul>
            </nav>
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

const props = defineProps({
    materiales: { type: Object, required: true },
    areas:      { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
})

const busqueda  = ref(props.filters.busqueda ?? '')
const area_id = ref(props.filters.area_id ?? '')

function buscar() {
    router.get(route('materiales.index'), { busqueda: busqueda.value, area_id: area_id.value }, { preserveState: true })
}
</script>

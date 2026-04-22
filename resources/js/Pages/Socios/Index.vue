<template>
    <Head title="Socios" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-people mr-2"></i>Socios</h3>
                <Link :href="route('socios.create')" class="btn btn-success">
                    <i class="bi bi-person-plus mr-1"></i>Nuevo
                </Link>
            </div>

            <FlashMessage />

            <!-- Filtros -->
            <form @submit.prevent="buscar" class="form-inline mb-3">
                <input v-model="search" type="text" class="form-control mr-2" placeholder="Nombre, apellido o email…">
                <select v-model="activo" class="form-control mr-2">
                    <option value="">Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
                <button type="submit" class="btn btn-outline-success mr-2">Buscar</button>
                <Link :href="route('socios.index')" class="btn btn-outline-secondary">Limpiar</Link>
            </form>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Año / División</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="socio in socios.data" :key="socio.id">
                            <td>{{ socio.apellido }}, {{ socio.nombre }}</td>
                            <td>{{ socio.email }}</td>
                            <td>{{ socio.anio }}° {{ socio.division }}</td>
                            <td>
                                <span :class="socio.activo ? 'badge badge-success' : 'badge badge-secondary'">
                                    {{ socio.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('socios.edit', socio.id)" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!socios.data.length">
                            <td colspan="5" class="text-center text-muted">No se encontraron socios.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <nav v-if="socios.last_page > 1">
                <ul class="pagination justify-content-center">
                    <li v-for="link in socios.links" :key="link.label"
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
    socios:  { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')
const activo = ref(props.filters.activo ?? '')

function buscar() {
    router.get(route('socios.index'), { search: search.value, activo: activo.value }, { preserveState: true })
}
</script>

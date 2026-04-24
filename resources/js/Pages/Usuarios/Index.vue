<template>
    <Head title="Usuarios" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-people mr-2"></i>Usuarios</h3>
                <Link :href="route('usuarios.create')" class="btn btn-success">
                    <i class="bi bi-person-plus mr-1"></i>Nuevo
                </Link>
            </div>

            <FlashMessage />

            <form @submit.prevent="buscar" class="form-inline mb-3">
                <input v-model="search" type="text" class="form-control mr-2" placeholder="Nombre, email o usuario…">
                <select v-model="rolFiltro" class="form-control mr-2" style="width:auto;">
                    <option value="">Todos los roles</option>
                    <option value="admin">Admin</option>
                    <option value="bibliotecario">Bibliotecario</option>
                    <option value="alumno">Alumno</option>
                </select>
                <button type="submit" class="btn btn-outline-success mr-2">Buscar</button>
                <Link :href="route('usuarios.index')" class="btn btn-outline-secondary">Limpiar</Link>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Socio vinculado</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in usuarios.data" :key="u.id">
                            <td>{{ u.nombre }}</td>
                            <td>{{ u.email }}</td>
                            <td><code>{{ u.usuario }}</code></td>
                            <td>
                                <span :class="badgeRol(u.rol)">{{ u.rol }}</span>
                            </td>
                            <td>
                                <span v-if="u.rol === 'alumno'">
                                    <span v-if="u.socio" class="badge badge-info">
                                        <i class="bi bi-person-check mr-1"></i>{{ u.socio }}
                                    </span>
                                    <span v-else class="badge badge-warning">Sin vincular</span>
                                </span>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <td>
                                <span :class="u.activo ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ u.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('usuarios.edit', u.id)" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!usuarios.data.length">
                            <td colspan="7" class="text-center text-muted">No se encontraron usuarios.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="usuarios.last_page > 1">
                <ul class="pagination justify-content-center">
                    <li v-for="link in usuarios.links" :key="link.label"
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
    usuarios: { type: Object, required: true },
    filters:  { type: Object, default: () => ({}) },
})

const search   = ref(props.filters.search ?? '')
const rolFiltro = ref(props.filters.rol ?? '')

function buscar() {
    router.get(route('usuarios.index'), { search: search.value, rol: rolFiltro.value }, { preserveState: true, replace: true })
}

function badgeRol(rol) {
    return {
        admin:        'badge badge-primary',
        bibliotecario:'badge badge-secondary',
        alumno:       'badge badge-success',
        'sin rol':    'badge badge-light',
    }[rol] ?? 'badge badge-light'
}
</script>

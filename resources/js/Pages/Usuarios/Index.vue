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

            <!-- Solicitudes pendientes de aprobación -->
            <div v-if="pendientes.length" class="card border-warning mb-4">
                <div class="card-header d-flex align-items-center" style="background: #fff8e1;">
                    <i class="bi bi-hourglass-split text-warning mr-2"></i>
                    <strong class="text-warning">Solicitudes pendientes de aprobación</strong>
                    <span class="badge badge-warning ml-2">{{ pendientes.length }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Usuario</th>
                                <th>Rol solicitado</th>
                                <th>Registrado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in pendientes" :key="p.id">
                                <td>{{ p.nombre }}</td>
                                <td>{{ p.email }}</td>
                                <td><code>{{ p.usuario }}</code></td>
                                <td>
                                    <span :class="badgeRol(p.rol)">{{ p.rol }}</span>
                                </td>
                                <td class="text-muted small">{{ p.creado_at }}</td>
                                <td class="text-right">
                                    <button
                                        class="btn btn-sm btn-success"
                                        :disabled="aprobando === p.id"
                                        @click="aprobar(p)"
                                    >
                                        <span v-if="aprobando === p.id" class="spinner-border spinner-border-sm mr-1"></span>
                                        <i v-else class="bi bi-check-lg"></i>
                                        <span class="d-none d-md-inline ml-1">Aprobar</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form @submit.prevent="buscar" class="mb-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-md-5 mb-2">
                        <label for="filtro-usuarios" class="sr-only">Buscar por nombre, email o usuario</label>
                        <input id="filtro-usuarios" v-model="busqueda" type="text" class="form-control" placeholder="Nombre, email o usuario…">
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 mb-2 d-flex">
                        <button type="submit" class="btn btn-outline-success mr-2" :disabled="cargando">
                            <span v-if="cargando" class="spinner-border spinner-border-sm mr-1"></span>
                            <i v-else class="bi bi-search mr-1"></i>
                            Buscar
                        </button>
                        <Link :href="route('usuarios.index')" class="btn btn-outline-secondary">Limpiar</Link>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col" class="d-none d-md-table-cell">Email</th>
                                <th scope="col" class="d-none d-lg-table-cell">Usuario</th>
                                <th scope="col">Socio vinculado</th>
                                <th scope="col">Estado</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                    <tbody>
                        <tr v-for="u in usuarios.data" :key="u.id">
                            <td>{{ u.nombre }}</td>
                            <td class="d-none d-md-table-cell">{{ u.email }}</td>
                            <td class="d-none d-lg-table-cell"><code>{{ u.usuario }}</code></td>
                            <td>
                                <span v-if="u.rol === 'alumno'">
                                    <span v-if="u.socio" class="badge badge-info">
                                        <i class="bi bi-person-check mr-1"></i>{{ u.socio }}
                                    </span>
                                    <span v-else class="badge badge-warning">Pendiente de Alta</span>
                                </span>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <td>
                                <span :class="u.activo ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ u.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('usuarios.edit', u.id)" class="btn btn-sm btn-outline-primary" aria-label="Editar usuario">
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

            <Pagination :links="usuarios.links" />
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
    usuarios:   { type: Object, required: true },
    filters:    { type: Object, default: () => ({}) },
    pendientes: { type: Array,  default: () => [] },
})

const busqueda    = ref(props.filters.busqueda ?? '')
const aprobando   = ref(null)
const cargando    = ref(false)

function buscar() {
    cargando.value = true
    router.get(route('usuarios.index'), { busqueda: busqueda.value }, {
        preserveState: true,
        replace: true,
        onFinish: () => { cargando.value = false },
    })
}

function aprobar(usuario) {
    aprobando.value = usuario.id
    router.patch(route('usuarios.aprobar', usuario.id), {}, {
        preserveScroll: true,
        onFinish: () => { aprobando.value = null },
    })
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

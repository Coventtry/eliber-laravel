<template>
    <Head title="Usuarios" />

    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Usuarios</h4>
                <small class="text-muted">Administrá las cuentas y sus roles</small>
            </div>
            <button class="btn btn-primary btn-sm" @click="abrirModal()">
                <i class="bi bi-plus-lg mr-1"></i>Nuevo usuario
            </button>
        </div>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input
                            v-model="filtros.search"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por nombre, email o usuario..."
                            @input="buscar"
                        >
                    </div>
                    <div class="col-md-3">
                        <select v-model="filtros.rol" class="form-control form-control-sm" @change="buscar">
                            <option value="">Todos los roles</option>
                            <option v-for="r in roles" :key="r" :value="r">{{ labelRol(r) }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" @click="limpiarFiltros">
                            <i class="bi bi-x-circle mr-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card border-0 shadow-sm">
            <div v-if="usuarios.data.length === 0" class="p-4 text-center text-muted">
                No hay usuarios que coincidan con los filtros.
            </div>
            <div v-else class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in usuarios.data" :key="u.id">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img v-if="u.picture_url" :src="u.picture_url"
                                        class="rounded-circle mr-2" width="32" height="32"
                                        style="object-fit:cover;">
                                    <div v-else class="avatar-placeholder mr-2" :style="avatarStyle(u.nombre)">
                                        {{ u.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="font-weight-bold" style="font-size:.9rem;">{{ u.nombre }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">@{{ u.usuario }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle small">{{ u.email }}</td>
                            <td class="align-middle">
                                <span class="badge" :class="badgeRol(u.rol)">{{ labelRol(u.rol) }}</span>
                            </td>
                            <td class="align-middle">
                                <span class="badge" :class="u.activo ? 'badge-success' : 'badge-secondary'">
                                    {{ u.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="align-middle text-right text-nowrap">
                                <button class="btn btn-sm btn-outline-primary mr-1" @click="abrirModal(u)" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary mr-1"
                                    @click="toggleActivo(u)" :title="u.activo ? 'Desactivar' : 'Activar'">
                                    <i :class="['bi', u.activo ? 'bi-toggle-on text-success' : 'bi-toggle-off']"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" @click="confirmarEliminar(u)" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="usuarios.last_page > 1" class="card-footer bg-white">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: !usuarios.prev_page_url }">
                        <Link class="page-link" :href="usuarios.prev_page_url ?? '#'">‹</Link>
                    </li>
                    <li v-for="p in usuarios.last_page" :key="p"
                        class="page-item" :class="{ active: p === usuarios.current_page }">
                        <Link class="page-link" :href="pageUrl(p)">{{ p }}</Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !usuarios.next_page_url }">
                        <Link class="page-link" :href="usuarios.next_page_url ?? '#'">›</Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ─── Modal crear / editar ─── -->
    <div v-if="modal.open" class="modal d-block" tabindex="-1"
        style="background:rgba(0,0,0,.5);" @click.self="cerrarModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ modal.usuario ? 'Editar usuario' : 'Nuevo usuario' }}</h5>
                    <button type="button" class="close" @click="cerrarModal"><span>&times;</span></button>
                </div>

                <form @submit.prevent="guardar" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Foto -->
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <img v-if="preview || form.picture_url"
                                        :src="preview || form.picture_url"
                                        class="rounded-circle border"
                                        style="width:90px;height:90px;object-fit:cover;">
                                    <div v-else class="avatar-placeholder-lg" :style="avatarStyle(form.nombre || '?')">
                                        {{ (form.nombre || '?').charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="btn btn-sm btn-outline-secondary mb-0" style="cursor:pointer;">
                                        <i class="bi bi-camera mr-1"></i>Foto
                                        <input type="file" class="d-none" accept="image/*" @change="onFoto">
                                    </label>
                                </div>
                            </div>

                            <!-- Datos -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small">Nombre completo <span class="text-danger">*</span></label>
                                        <input v-model="form.nombre" type="text" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small">Usuario <span class="text-danger">*</span></label>
                                        <input v-model="form.usuario" type="text" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small">Email <span class="text-danger">*</span></label>
                                        <input v-model="form.email" type="email" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small">
                                            Contraseña
                                            <span v-if="!modal.usuario" class="text-danger">*</span>
                                            <span v-else class="text-muted font-weight-normal">(dejar vacío para no cambiar)</span>
                                        </label>
                                        <input v-model="form.password" type="password" class="form-control form-control-sm"
                                            :required="!modal.usuario" autocomplete="new-password">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small">Rol <span class="text-danger">*</span></label>
                                        <select v-model="form.rol" class="form-control form-control-sm" required>
                                            <option v-for="r in roles" :key="r" :value="r">{{ labelRol(r) }}</option>
                                        </select>
                                    </div>
                                    <!-- socio_id solo para alumno -->
                                    <div v-if="form.rol === 'alumno'" class="col-md-6 form-group">
                                        <label class="font-weight-bold small">Vincular socio</label>
                                        <select v-model="form.socio_id" class="form-control form-control-sm">
                                            <option :value="null">— Sin vincular —</option>
                                            <option v-for="s in socios" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                                        </select>
                                        <small class="text-muted">Necesario para que el alumno vea sus reservas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="guardando">
                            <i class="bi bi-check2 mr-1"></i>{{ modal.usuario ? 'Guardar cambios' : 'Crear usuario' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal confirmar eliminar -->
    <div v-if="usuarioAEliminar" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">¿Eliminar a <strong>{{ usuarioAEliminar.nombre }}</strong>?</p>
                    <small class="text-muted">Esta acción no se puede deshacer.</small>
                </div>
                <div class="modal-footer justify-content-center py-2">
                    <button class="btn btn-sm btn-secondary" @click="usuarioAEliminar = null">Cancelar</button>
                    <button class="btn btn-sm btn-danger" @click="eliminar">Eliminar</button>
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
import { ref, reactive } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    usuarios: { type: Object, required: true },
    socios:   { type: Array,  default: () => [] },
    roles:    { type: Array,  default: () => [] },
    filters:  { type: Object, default: () => ({}) },
})

const page = usePage()

// ─── Filtros ───
const filtros  = reactive({ search: props.filters.search ?? '', rol: props.filters.rol ?? '' })

function buscar() {
    router.get(route('admin.usuarios'), {
        search: filtros.search || undefined,
        rol:    filtros.rol    || undefined,
    }, { preserveState: true, replace: true })
}

function limpiarFiltros() {
    filtros.search = ''
    filtros.rol    = ''
    buscar()
}

function pageUrl(p) {
    const url = new URL(page.url, window.location.origin)
    url.searchParams.set('page', p)
    return url.pathname + url.search
}

// ─── Modal ───
const modal    = reactive({ open: false, usuario: null })
const form     = reactive({ nombre: '', email: '', usuario: '', password: '', rol: 'bibliotecario', socio_id: null, picture_url: null })
const preview  = ref(null)
const fotoFile = ref(null)
const guardando = ref(false)
const usuarioAEliminar = ref(null)

function abrirModal(u = null) {
    modal.usuario = u
    form.nombre    = u?.nombre    ?? ''
    form.email     = u?.email     ?? ''
    form.usuario   = u?.usuario   ?? ''
    form.password  = ''
    form.rol       = u?.rol       ?? 'bibliotecario'
    form.socio_id  = u?.socio_id  ?? null
    form.picture_url = u?.picture_url ?? null
    preview.value  = null
    fotoFile.value = null
    modal.open     = true
}

function cerrarModal() {
    modal.open = false
    guardando.value = false
}

function onFoto(e) {
    const file = e.target.files[0]
    if (!file) return
    fotoFile.value = file
    preview.value  = URL.createObjectURL(file)
}

function guardar() {
    guardando.value = true
    const data = new FormData()
    data.append('nombre',   form.nombre)
    data.append('email',    form.email)
    data.append('usuario',  form.usuario)
    data.append('rol',      form.rol)
    if (form.password) data.append('password', form.password)
    if (form.rol === 'alumno' && form.socio_id) data.append('socio_id', form.socio_id)
    if (fotoFile.value) data.append('picture', fotoFile.value)

    if (modal.usuario) {
        data.append('_method', 'PUT')
        router.post(route('admin.usuarios.update', modal.usuario.id), data, {
            forceFormData: true,
            onSuccess: cerrarModal,
            onError:   () => { guardando.value = false },
        })
    } else {
        router.post(route('admin.usuarios.store'), data, {
            forceFormData: true,
            onSuccess: cerrarModal,
            onError:   () => { guardando.value = false },
        })
    }
}

function toggleActivo(u) {
    router.patch(route('admin.usuarios.toggle', u.id))
}

function confirmarEliminar(u) {
    usuarioAEliminar.value = u
}

function eliminar() {
    router.delete(route('admin.usuarios.destroy', usuarioAEliminar.value.id), {
        onSuccess: () => { usuarioAEliminar.value = null },
    })
}

// ─── Helpers ───
const AVATAR_COLORS = ['#4f46e5','#0891b2','#16a34a','#dc2626','#d97706','#7c3aed']
function avatarStyle(nombre) {
    const idx = nombre.charCodeAt(0) % AVATAR_COLORS.length
    return `background:${AVATAR_COLORS[idx]};color:#fff;`
}

function labelRol(r) {
    return { admin: 'Admin', bibliotecario: 'Bibliotecario', alumno: 'Alumno' }[r] ?? r
}

function badgeRol(r) {
    return { admin: 'badge-danger', bibliotecario: 'badge-primary', alumno: 'badge-info' }[r] ?? 'badge-secondary'
}
</script>

<style scoped>
.avatar-placeholder {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center; justify-content: center;
    font-size: .8rem; font-weight: 700;
    flex-shrink: 0;
}
.avatar-placeholder-lg {
    width: 90px; height: 90px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 700;
    margin: 0 auto;
}
</style>

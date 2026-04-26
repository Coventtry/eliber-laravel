<template>
    <Head :title="`Editar: ${usuario.nombre}`" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 680px; margin: 0 auto;">
            <div class="d-flex align-items-center mb-4">
                <h3 class="mb-0 mr-3">
                    <i class="bi bi-person-gear mr-2"></i>{{ usuario.nombre }}
                </h3>
                <span :class="badgeRol">{{ labelRol }}</span>
                <span :class="usuario.activo ? 'badge badge-success ml-2' : 'badge badge-danger ml-2'">
                    {{ usuario.activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>

            <FlashMessage />

            <!-- Datos básicos -->
            <div class="card mb-4">
                <div class="card-header"><strong>Datos del usuario</strong></div>
                <div class="card-body">
                    <form @submit.prevent="submitInfo">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input v-model="form.nombre" type="text" class="form-control"
                                       :class="{ 'is-invalid': form.errors.nombre }">
                                <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Apellido</label>
                                <input v-model="form.apellido" type="text" class="form-control"
                                       :class="{ 'is-invalid': form.errors.apellido }">
                                <div class="invalid-feedback">{{ form.errors.apellido }}</div>
                            </div>
                        </div>

                        <!-- Año y División (solo alumno) -->
                        <div v-if="esAlumnoTarget" class="form-row">
                            <div class="form-group col-md-6">
                                <label>Año</label>
                                <select v-model.number="form.anio" class="form-control"
                                        :class="{ 'is-invalid': form.errors.anio }">
                                    <option :value="null">— Sin asignar —</option>
                                    <option v-for="n in 6" :key="n" :value="n">{{ n }}°</option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.anio }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>División</label>
                                <select v-model.number="form.division" class="form-control"
                                        :class="{ 'is-invalid': form.errors.division }">
                                    <option :value="null">— Sin asignar —</option>
                                    <option v-for="n in 6" :key="n" :value="n">{{ n }}</option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.division }}</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input v-model="form.email" type="email" class="form-control"
                                   :class="{ 'is-invalid': form.errors.email }">
                            <div class="invalid-feedback">{{ form.errors.email }}</div>
                        </div>

                        <div class="form-group">
                            <label>Usuario <span class="text-danger">*</span></label>
                            <input v-model="form.usuario" type="text" class="form-control"
                                   :class="{ 'is-invalid': form.errors.usuario }">
                            <div class="invalid-feedback">{{ form.errors.usuario }}</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nueva contraseña</label>
                                <input v-model="form.password" type="password" class="form-control"
                                       :class="{ 'is-invalid': form.errors.password }"
                                       placeholder="Dejar vacío para no cambiar">
                                <div class="invalid-feedback">{{ form.errors.password }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Confirmar contraseña</label>
                                <input v-model="form.password_confirmation" type="password" class="form-control">
                            </div>
                        </div>

                        <!-- Socio vinculado (solo alumno) -->
                        <div v-if="esAlumnoTarget" class="form-group">
                            <label>Socio vinculado</label>
                            <div class="form-control-plaintext">
                                <span v-if="usuario.socio_id" class="badge badge-info">
                                    <i class="bi bi-person-check mr-1"></i>Vinculado — ID {{ usuario.socio_id }}
                                </span>
                                <span v-else class="badge badge-warning">
                                    <i class="bi bi-hourglass-split mr-1"></i>Sin vincular
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-2">
                            <Link :href="route('usuarios.index')" class="btn btn-outline-secondary">Cancelar</Link>
                            <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Permisos (solo bibliotecario) -->
            <div v-if="!esAdminTarget && !esAlumnoTarget" class="card mb-4">
                <div class="card-header"><strong>Permisos</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Los permisos marcados están activos para este usuario.</p>
                    <div class="row">
                        <div v-for="permiso in todosPermisos" :key="permiso" class="col-md-6 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="`perm-${permiso}`"
                                    :value="permiso"
                                    v-model="permisosSeleccionados"
                                >
                                <label class="custom-control-label" :for="`perm-${permiso}`">
                                    {{ permiso }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <button @click="guardarPermisos" class="btn btn-primary mt-3" :disabled="savingPermisos">
                        Guardar permisos
                    </button>
                </div>
            </div>
            <div v-else-if="esAdminTarget" class="alert alert-info mb-4">
                El rol <strong>admin</strong> tiene acceso total. Los permisos individuales no aplican.
            </div>

            <!-- Alta de Socio (alumno sin socio vinculado) -->
            <div v-if="esAlumnoTarget && !usuario.socio_id" class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-person-plus mr-1"></i><strong>Alta en el sistema</strong>
                </div>
                <div class="card-body">
                    <div v-if="!usuario.activo" class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-hourglass-split mr-1"></i>
                        Cuenta <strong>inactiva</strong> — el alumno no puede ingresar todavía.
                    </div>
                    <p class="text-muted small mb-3">
                        Al dar de alta se activa la cuenta y se crea automáticamente el registro de Socio, habilitando préstamos y reservas.
                    </p>
                    <button @click="darDeAlta" class="btn btn-success" :disabled="aprobando">
                        <span v-if="aprobando" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="bi bi-check-circle mr-1"></i>
                        Dar de Alta
                    </button>
                </div>
            </div>

            <!-- Socio ya vinculado: info -->
            <div v-if="esAlumnoTarget && usuario.socio_id" class="card mb-4">
                <div class="card-header"><strong>Socio vinculado</strong></div>
                <div class="card-body py-2">
                    <span class="badge badge-info">
                        <i class="bi bi-person-check mr-1"></i>ID Socio: {{ usuario.socio_id }}
                    </span>
                    <Link :href="route('socios.index')" class="btn btn-sm btn-outline-secondary ml-3">
                        <i class="bi bi-people mr-1"></i>Ver en Panel de Socios
                    </Link>
                </div>
            </div>

            <!-- Activar / Desactivar (no para uno mismo) -->
            <div v-if="!esYo" class="card">
                <div class="card-header"><strong>Estado de la cuenta</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        {{ usuario.activo ? 'El usuario puede iniciar sesión.' : 'El usuario no puede iniciar sesión.' }}
                    </p>
                    <button @click="toggleActivo" class="btn"
                            :class="usuario.activo ? 'btn-outline-danger' : 'btn-outline-success'">
                        {{ usuario.activo ? 'Desactivar usuario' : 'Activar usuario' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    usuario:          { type: Object, required: true },
    permisos_usuario: { type: Array,  default: () => [] },
    todos_permisos:   { type: Array,  default: () => [] },
    es_admin_target:  { type: Boolean, default: false },
    es_alumno_target: { type: Boolean, default: false },
    es_yo:            { type: Boolean, default: false },
    socios:           { type: Array,  default: () => [] },
})

const form = useForm({
    nombre:                props.usuario.nombre,
    apellido:              props.usuario.apellido ?? '',
    email:                 props.usuario.email,
    usuario:               props.usuario.usuario,
    password:              '',
    password_confirmation: '',
    anio:                  props.usuario.anio ?? '',
    division:              props.usuario.division ?? '',
})

const esAdminTarget          = ref(props.es_admin_target)
const esAlumnoTarget         = ref(props.es_alumno_target)
const esYo                   = ref(props.es_yo)
const permisosSeleccionados  = ref([...props.permisos_usuario])
const savingPermisos         = ref(false)
const aprobando              = ref(false)

const ROLES = { admin: 'Administrador', bibliotecario: 'Bibliotecario', alumno: 'Alumno' }
const labelRol = computed(() => ROLES[props.usuario.rol] ?? props.usuario.rol ?? '')

const badgeRol = computed(() => ({
    admin:        'badge badge-primary',
    bibliotecario:'badge badge-secondary',
    alumno:       'badge badge-success',
}[props.usuario.rol] ?? 'badge badge-light'))

function submitInfo() {
    form.put(route('usuarios.update', props.usuario.id))
}

function guardarPermisos() {
    savingPermisos.value = true
    router.patch(
        route('usuarios.permisos', props.usuario.id),
        { permisos: permisosSeleccionados.value },
        { onFinish: () => { savingPermisos.value = false } }
    )
}

function toggleActivo() {
    router.patch(route('usuarios.toggle-activo', props.usuario.id))
}

function darDeAlta() {
    aprobando.value = true
    router.patch(route('usuarios.aprobar', props.usuario.id), {}, {
        onFinish: () => { aprobando.value = false },
    })
}
</script>

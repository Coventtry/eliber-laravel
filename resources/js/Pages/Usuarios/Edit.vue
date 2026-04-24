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
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input v-model="form.nombre" type="text" class="form-control"
                                   :class="{ 'is-invalid': form.errors.nombre }">
                            <div class="invalid-feedback">{{ form.errors.nombre }}</div>
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
                            <select v-model="form.socio_id" class="form-control"
                                    :class="{ 'is-invalid': form.errors.socio_id }">
                                <option :value="null">— Sin vincular —</option>
                                <option v-for="s in socios" :key="s.id" :value="s.id">{{ s.label }}</option>
                            </select>
                            <small class="form-text text-muted">Vinculá este usuario al registro de socio correspondiente.</small>
                            <div class="invalid-feedback">{{ form.errors.socio_id }}</div>
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

            <!-- Activar / Desactivar (no para uno mismo) -->
            <div v-if="!esYo" class="card">
                <div class="card-header"><strong>Estado de la cuenta</strong></div>
                <div class="card-body">
                    <p class="text-muted small">
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
    email:                 props.usuario.email,
    usuario:               props.usuario.usuario,
    password:              '',
    password_confirmation: '',
    socio_id:              props.usuario.socio_id ?? null,
})

const esAdminTarget          = ref(props.es_admin_target)
const esAlumnoTarget         = ref(props.es_alumno_target)
const esYo                   = ref(props.es_yo)
const permisosSeleccionados  = ref([...props.permisos_usuario])
const savingPermisos         = ref(false)

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
</script>

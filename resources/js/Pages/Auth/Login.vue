<template>
    <Head :title="tab === 'login' ? 'Iniciar sesión' : 'Crear cuenta'" />

    <div class="d-flex align-items-center justify-content-center min-vh-100 login-container">
        <div class="card login-card shadow-lg">

            <!-- Pestañas -->
            <div class="card-header p-0 border-0 bg-transparent">
                <ul class="nav nav-tabs nav-fill" style="border-bottom: 2px solid var(--eliber-primary);">
                    <li class="nav-item">
                        <button
                            class="nav-link w-100 fw-semibold"
                            :class="tab === 'login' ? 'active' : 'text-muted'"
                            style="border-radius: 0; border: none;"
                            :style="tab === 'login' ? 'border-bottom: 2px solid var(--eliber-primary); color: var(--eliber-primary);' : ''"
                            @click="cambiarPestana('login')"
                        >
                            <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link w-100 fw-semibold"
                            :class="tab === 'register' ? 'active' : 'text-muted'"
                            style="border-radius: 0; border: none;"
                            :style="tab === 'register' ? 'border-bottom: 2px solid var(--eliber-primary); color: var(--eliber-primary);' : ''"
                            @click="cambiarPestana('register')"
                        >
                            <i class="bi bi-person-plus-fill me-1"></i> Registrarse
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="/img/logo.png" alt="E-liber" height="50" class="logo-img mb-3"><br>
                    <small class="text-muted">Sistema de Gestión Bibliotecaria</small>
                </div>

                <!-- Mensaje pendiente de aprobación -->
                <div v-if="flashInfo" class="alert alert-info d-flex align-items-start mb-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                    <span>{{ flashInfo }}</span>
                </div>

                <!-- ── TAB LOGIN ── -->
                <form v-if="tab === 'login'" @submit.prevent="enviarLogin">
                    <div class="form-group">
                        <label for="usuario" class="fw-semibold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-primary">
                                <i class="bi bi-person-fill input-group-icon"></i>
                            </span>
                            <input
                                id="usuario"
                                v-model="loginForm.usuario"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.usuario }"
                                autocomplete="username"
                                autofocus
                                placeholder="Ingresa tu usuario"
                            >
                        </div>
                        <div v-if="errors.usuario" class="invalid-feedback d-block">{{ errors.usuario }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--eliber-crema); border-color: var(--eliber-primary);">
                                <i class="bi bi-lock-fill input-group-icon"></i>
                            </span>
                            <input
                                id="password"
                                v-model="loginForm.password"
                                :type="verPassword ? 'text' : 'password'"
                                class="form-control"
                                :class="{ 'is-invalid': errors.password }"
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                            >
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" @click="verPassword = !verPassword" tabindex="-1" :aria-label="verPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                    <i :class="verPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div v-if="errors.password" class="invalid-feedback d-block">{{ errors.password }}</div>
                    </div>

                    <div class="form-group form-check">
                        <input id="remember" v-model="loginForm.remember" type="checkbox" class="form-check-input">
                        <label for="remember" class="form-check-label">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-success btn-block py-2 mt-3" :disabled="loginForm.processing">
                        <span v-if="loginForm.processing" class="spinner-border spinner-border-sm mr-2"></span>
                        <i v-else class="bi bi-box-arrow-in-right me-2"></i>
                        Ingresar
                    </button>
                </form>

                <!-- ── TAB REGISTRO ── -->
                <form v-else @submit.prevent="enviarRegistro">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="reg-nombre" class="fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input
                                id="reg-nombre"
                                v-model="regForm.nombre"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': regErrors.nombre }"
                                placeholder="Tu nombre"
                                autocomplete="given-name"
                            >
                            <div v-if="regErrors.nombre" class="invalid-feedback d-block">{{ regErrors.nombre }}</div>
                        </div>
                        <div class="form-group col-6">
                            <label for="reg-apellido" class="fw-semibold">Apellido <span class="text-danger">*</span></label>
                            <input
                                id="reg-apellido"
                                v-model="regForm.apellido"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': regErrors.apellido }"
                                placeholder="Tu apellido"
                                autocomplete="family-name"
                            >
                            <div v-if="regErrors.apellido" class="invalid-feedback d-block">{{ regErrors.apellido }}</div>
                        </div>
                    </div>

                    <!-- Año y División (solo alumno) -->
                    <div v-if="regForm.rol === 'alumno'" class="form-row">
                        <div class="form-group col-6">
                            <label for="reg-anio" class="fw-semibold">Año <span class="text-danger">*</span></label>
                            <select id="reg-anio" v-model.number="regForm.anio"
                                    class="form-control" :class="{ 'is-invalid': regErrors.anio }">
                                <option value="">— Seleccioná —</option>
                                <option v-for="n in 6" :key="n" :value="n">{{ n }}°</option>
                            </select>
                            <div v-if="regErrors.anio" class="invalid-feedback d-block">{{ regErrors.anio }}</div>
                        </div>
                        <div class="form-group col-6">
                            <label for="reg-division" class="fw-semibold">División <span class="text-danger">*</span></label>
                            <select id="reg-division" v-model.number="regForm.division"
                                    class="form-control" :class="{ 'is-invalid': regErrors.division }">
                                <option value="">— Seleccioná —</option>
                                <option v-for="n in 6" :key="n" :value="n">{{ n }}</option>
                            </select>
                            <div v-if="regErrors.division" class="invalid-feedback d-block">{{ regErrors.division }}</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg-email" class="fw-semibold">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-primary">
                                <i class="bi bi-envelope-fill input-group-icon"></i>
                            </span>
                            <input
                                id="reg-email"
                                v-model="regForm.email"
                                type="email"
                                class="form-control"
                                :class="{ 'is-invalid': regErrors.email }"
                                placeholder="correo@ejemplo.com"
                                autocomplete="email"
                            >
                        </div>
                        <div v-if="regErrors.email" class="invalid-feedback d-block">{{ regErrors.email }}</div>
                    </div>

                    <div class="form-group">
                        <label for="reg-usuario" class="fw-semibold">Nombre de usuario</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-primary">
                                <i class="bi bi-at input-group-icon"></i>
                            </span>
                            <input
                                id="reg-usuario"
                                v-model="regForm.usuario"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': regErrors.usuario }"
                                placeholder="sin espacios ni caracteres especiales"
                                autocomplete="username"
                            >
                        </div>
                        <div v-if="regErrors.usuario" class="invalid-feedback d-block">{{ regErrors.usuario }}</div>
                    </div>

                    <div class="form-group">
                        <label class="fw-semibold">Tipo de cuenta</label>
                        <div class="d-flex gap-3 mt-1">
                            <div
                                class="rol-option flex-fill text-center py-2 px-3 rounded border"
                                :class="regForm.rol === 'alumno' ? 'rol-selected' : ''"
                                 @click="regForm.rol = 'alumno'"
                            >
                                <i class="bi bi-mortarboard-fill d-block fs-4 mb-1 input-group-icon"></i>
                                <span class="fw-semibold small">Alumno</span>
                            </div>
                            <div
                                class="rol-option flex-fill text-center py-2 px-3 rounded border"
                                :class="regForm.rol === 'bibliotecario' ? 'rol-selected' : ''"
                                 @click="regForm.rol = 'bibliotecario'"
                            >
                                <i class="bi bi-book-fill d-block fs-4 mb-1 input-group-icon"></i>
                                <span class="fw-semibold small">Bibliotecario</span>
                            </div>
                        </div>
                        <div v-if="regErrors.rol" class="text-danger small mt-1">{{ regErrors.rol }}</div>
                    </div>

                    <div class="form-group">
                        <label for="reg-institucion" class="fw-semibold">Institución</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-primary">
                                <i class="bi bi-building input-group-icon"></i>
                            </span>
                            <select
                                id="reg-institucion"
                                v-model="regForm.institucion_id"
                                class="form-control"
                                :class="{ 'is-invalid': regErrors.institucion_id }"
                            >
                                <option value="" disabled>Seleccioná tu institución</option>
                                <option v-for="inst in instituciones" :key="inst.id" :value="inst.id">
                                    {{ inst.nombre }}
                                </option>
                            </select>
                        </div>
                        <div v-if="regErrors.institucion_id" class="invalid-feedback d-block">{{ regErrors.institucion_id }}</div>
                    </div>

                    <div class="form-group">
                        <label for="reg-password" class="fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-primary">
                                <i class="bi bi-lock-fill input-group-icon"></i>
                            </span>
                            <input
                                id="reg-password"
                                v-model="regForm.password"
                                :type="verRegPassword ? 'text' : 'password'"
                                class="form-control"
                                :class="{ 'is-invalid': regErrors.password }"
                                placeholder="Mínimo 8 caracteres"
                                autocomplete="new-password"
                            >
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" @click="verRegPassword = !verRegPassword" tabindex="-1" :aria-label="verRegPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                    <i :class="verRegPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div v-if="regErrors.password" class="invalid-feedback d-block">{{ regErrors.password }}</div>
                    </div>

                    <div class="form-group">
                        <label for="reg-password-confirm" class="fw-semibold">Confirmar contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-primary">
                                <i class="bi bi-lock-fill input-group-icon"></i>
                            </span>
                            <input
                                id="reg-password-confirm"
                                v-model="regForm.password_confirmation"
                                :type="verRegPassword ? 'text' : 'password'"
                                class="form-control"
                                placeholder="Repetí la contraseña"
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-block py-2 mt-3" :disabled="regForm.processing">
                        <span v-if="regForm.processing" class="spinner-border spinner-border-sm mr-2"></span>
                        <i v-else class="bi bi-person-check-fill me-2"></i>
                        Crear cuenta
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top" style="border-color:rgba(45,90,39,.1);">
                    <small v-if="tab === 'login'">
                        <Link :href="route('password.reset')" class="forgot-link">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </small>
                    <div class="mt-2">
                        <small>
                            <Link :href="route('landing')" class="text-muted">
                                <i class="bi bi-arrow-left mr-1"></i>Volver al inicio
                            </Link>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    instituciones: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors)
const flashInfo = computed(() => page.props.flash?.info)

// Si hay errores y estamos en la página sin indicar tab,
// detectamos si son errores de registro (tienen campos del form de registro)
const registerFields = ['nombre', 'email', 'rol']
const hasRegisterErrors = computed(() =>
    registerFields.some(f => !!page.props.errors[f])
)

const tab = ref(hasRegisterErrors.value ? 'register' : 'login')
const verPassword    = ref(false)
const verRegPassword = ref(false)

function cambiarPestana(t) {
    tab.value = t
    loginForm.clearErrors()
    regForm.clearErrors()
}

// ── Login ──
const loginForm = useForm({
    usuario: '',
    password: '',
    remember: false,
})

function enviarLogin() {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    })
}

// ── Registro ──
const regForm = useForm({
    nombre:                '',
    apellido:              '',
    email:                 '',
    usuario:               '',
    rol:                   'alumno',
    institucion_id:        props.instituciones.length === 1 ? props.instituciones[0].id : '',
    anio:                  '',
    division:              '',
    password:              '',
    password_confirmation: '',
})

const regErrors = computed(() => {
    if (tab.value === 'register') return page.props.errors
    return {}
})

function enviarRegistro() {
    regForm.post(route('register'), {
        onFinish: () => regForm.reset('password', 'password_confirmation'),
    })
}
</script>

<style scoped>
.rol-option {
    border-color: #dee2e6 !important;
    transition: all 0.15s;
    cursor: pointer;
}
.rol-option:hover {
    border-color: var(--eliber-primary) !important;
    background: var(--eliber-crema);
}
.rol-selected {
    border-color: var(--eliber-primary) !important;
    background: var(--eliber-crema);
    box-shadow: 0 0 0 2px rgba(45, 90, 39, 0.2);
}

.login-card {
    width: 100%;
    max-width: 440px;
}

.input-group-icon {
    color: var(--eliber-primary);
}

.forgot-link {
    color: var(--eliber-secondary);
}
</style>

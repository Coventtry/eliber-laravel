<template>
    <Head title="Iniciar sesión" />

    <div class="d-flex align-items-center justify-content-center min-vh-100 login-container">
        <div class="card login-card shadow-lg" style="width: 100%; max-width: 420px;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="/img/logo.png" alt="E-liber" height="50" class="logo-img mb-3"><br>
                    <small class="text-muted">Sistema de Gestión Bibliotecaria</small>
                </div>

                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label for="usuario" class="fw-semibold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--eliber-crema); border-color: var(--eliber-primary);">
                                <i class="bi bi-person-fill" style="color: var(--eliber-primary);"></i>
                            </span>
                            <input
                                id="usuario"
                                v-model="form.usuario"
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
                                <i class="bi bi-lock-fill" style="color: var(--eliber-primary);"></i>
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                :class="{ 'is-invalid': errors.password }"
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                            >
                        </div>
                        <div v-if="errors.password" class="invalid-feedback d-block">{{ errors.password }}</div>
                    </div>

                    <div class="form-group form-check">
                        <input id="remember" v-model="form.remember" type="checkbox" class="form-check-input">
                        <label for="remember" class="form-check-label">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-success btn-block py-2 mt-3" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
                        <i v-else class="bi bi-box-arrow-in-right me-2"></i>
                        Ingresar
                    </button>
                </form>

                <div class="text-center mt-4 pt-3" style="border-top: 1px solid rgba(45,90,39,0.1);">
                    <small>
                        <Link :href="route('password.reset')" style="color: var(--eliber-secondary);">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </small>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const errors = computed(() => page.props.errors)

const form = useForm({
    usuario: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

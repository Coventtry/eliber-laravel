<template>
    <Head title="Restablecer contraseña" />

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2><i class="bi bi-key mr-2"></i>Restablecer contraseña</h2>
            </div>

            <div v-if="$page.props.flash?.success" class="alert alert-success">
                <i class="bi bi-check-circle mr-1"></i>
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" class="auth-form">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input
                        id="usuario"
                        v-model="form.usuario"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.usuario }"
                        placeholder="Ingresá tu usuario"
                        autofocus
                    >
                    <div v-if="errors.usuario" class="invalid-feedback">{{ errors.usuario }}</div>
                </div>

                <div class="form-group">
                    <label for="password">Nueva contraseña</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': errors.password }"
                        placeholder="Mínimo 6 caracteres"
                    >
                    <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control"
                        placeholder="Repetí la contraseña"
                    >
                </div>

                <button type="submit" class="btn btn-success btn-block" :disabled="processing">
                    <span v-if="processing">
                        <span class="spinner-border spinner-border-sm mr-1"></span>
                        Procesando...
                    </span>
                    <span v-else>
                        <i class="bi bi-check-lg mr-1"></i>Restablecer contraseña
                    </span>
                </button>
            </form>

            <div class="auth-footer">
                <Link :href="route('login')" class="btn btn-outline-secondary btn-sm btn-block">
                    <i class="bi bi-arrow-left mr-1"></i>Volver al login
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const form = useForm({
    usuario: '',
    password: '',
    password_confirmation: '',
})

const processing = form.processing
const errors = page.props.errors || {}

function submit() {
    form.post(route('password.reset.submit'))
}
</script>

<style scoped>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    padding: 1rem;
}

.auth-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    padding: 2rem;
}

.auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-header h2 {
    font-size: 1.5rem;
    color: #2e7d32;
}

.auth-form {
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.auth-footer {
    margin-top: 1rem;
}
</style>
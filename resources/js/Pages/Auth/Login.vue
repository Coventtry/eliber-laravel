<template>
    <Head title="Iniciar sesión" />

    <div class="d-flex align-items-center justify-content-center min-vh-100"
         style="background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 60%, #388e3c 100%)">
        <div class="card shadow-lg" style="width: 100%; max-width: 420px;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-book-half text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-2 font-weight-bold">E-liber</h4>
                    <small class="text-muted">Sistema de Gestión de Biblioteca</small>
                </div>

                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': errors.email }"
                            autocomplete="username"
                            autofocus
                        >
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': errors.password }"
                            autocomplete="current-password"
                        >
                        <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                    </div>

                    <div class="form-group form-check">
                        <input id="remember" v-model="form.remember" type="checkbox" class="form-check-input">
                        <label for="remember" class="form-check-label">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-success btn-block" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
                        Ingresar
                    </button>
                </form>
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
    email:    '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

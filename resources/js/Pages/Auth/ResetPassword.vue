<template>
    <Head title="Cambiar contraseña" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3"><i class="bi bi-key mr-2"></i>Cambiar contraseña</h4>

                        <div v-if="$page.props.flash?.success" class="alert alert-success">
                            <i class="bi bi-check-circle mr-1"></i>
                            {{ $page.props.flash.success }}
                        </div>

                        <form @submit.prevent="submit">
                            <div class="form-group">
                                <label for="current_password">Contraseña actual</label>
                                <input id="current_password" v-model="form.current_password" type="password"
                                    class="form-control" :class="{ 'is-invalid': errors.current_password }"
                                    placeholder="Ingresá tu contraseña actual" autofocus>
                                <div v-if="errors.current_password" class="invalid-feedback">{{ errors.current_password }}</div>
                            </div>

                            <div class="form-group">
                                <label for="password">Nueva contraseña</label>
                                <input id="password" v-model="form.password" type="password"
                                    class="form-control" :class="{ 'is-invalid': errors.password }"
                                    placeholder="Mínimo 8 caracteres">
                                <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirmar nueva contraseña</label>
                                <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                    class="form-control" :class="{ 'is-invalid': errors.password_confirmation }"
                                    placeholder="Repetí la nueva contraseña">
                                <div v-if="errors.password_confirmation" class="invalid-feedback">{{ errors.password_confirmation }}</div>
                            </div>

                            <button type="submit" class="btn btn-success btn-block" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm mr-1"></span>
                                <i v-else class="bi bi-check-lg mr-1"></i>Actualizar contraseña
                            </button>
                        </form>

                        <div class="mt-3 text-center">
                            <Link :href="route('perfil.edit')" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left mr-1"></i>Volver a mi perfil
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const errors = page.props.errors || {}

function submit() {
    form.post(route('password.reset.submit'))
}
</script>

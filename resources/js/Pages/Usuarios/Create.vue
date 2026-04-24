<template>
    <Head title="Nuevo usuario" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 600px; margin: 0 auto;">
            <h3 class="mb-4"><i class="bi bi-person-plus mr-2"></i>Nuevo usuario</h3>
            <FlashMessage />

            <form @submit.prevent="submit">
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
                        <label>Contraseña <span class="text-danger">*</span></label>
                        <input v-model="form.password" type="password" class="form-control"
                               :class="{ 'is-invalid': form.errors.password }">
                        <div class="invalid-feedback">{{ form.errors.password }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Confirmar contraseña <span class="text-danger">*</span></label>
                        <input v-model="form.password_confirmation" type="password" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Rol <span class="text-danger">*</span></label>
                    <select v-model="form.rol" class="form-control"
                            :class="{ 'is-invalid': form.errors.rol }">
                        <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                    </select>
                    <div class="invalid-feedback">{{ form.errors.rol }}</div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <Link :href="route('usuarios.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    roles: { type: Array, required: true },
})

const form = useForm({
    nombre: '',
    email: '',
    usuario: '',
    password: '',
    password_confirmation: '',
    rol: 'bibliotecario',
})

function submit() {
    form.post(route('usuarios.store'))
}
</script>

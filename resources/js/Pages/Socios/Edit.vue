<template>
    <Head :title="`Editar: ${socio.apellido}, ${socio.nombre}`" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width:640px;">
            <h3 class="mb-4">
                <i class="bi bi-person-gear mr-2"></i>
                {{ socio.apellido }}, {{ socio.nombre }}
                <span :class="socio.activo ? 'badge badge-success' : 'badge badge-secondary'" class="ml-2">
                    {{ socio.activo ? 'Activo' : 'Inactivo' }}
                </span>
            </h3>
            <FlashMessage />

            <form @submit.prevent="enviar">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input v-model="form.nombre" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.nombre }">
                        <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Apellido <span class="text-danger">*</span></label>
                        <input v-model="form.apellido" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.apellido }">
                        <div class="invalid-feedback">{{ form.errors.apellido }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input v-model="form.email" type="email" class="form-control"
                           :class="{ 'is-invalid': form.errors.email }">
                    <div class="invalid-feedback">{{ form.errors.email }}</div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Teléfono</label>
                        <input v-model="form.telefono" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Dirección</label>
                        <input v-model="form.direccion" type="text" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Año</label>
                        <input v-model.number="form.anio" type="number" min="1" max="6" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>División</label>
                        <input v-model.number="form.division" type="number" min="1" max="6" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <Link :href="route('socios.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar cambios</button>
                </div>
            </form>

            <hr class="my-4">

            <!-- Baja / Reactivar -->
            <div class="d-flex justify-content-between align-items-center">
<div v-if="socio.activo">
        <button class="btn btn-outline-danger" @click="mostrarConfirmBaja = true" :disabled="bajando">
            <i class="bi bi-person-dash mr-1"></i>Dar de baja
        </button>
        <div v-if="mostrarConfirmBaja" class="mt-2 p-3 border rounded">
            <p class="mb-2">¿Dar de baja a {{ socio.nombre }} {{ socio.apellido }}?</p>
            <button class="btn btn-secondary btn-sm mr-2" @click="mostrarConfirmBaja = false" :disabled="bajando">Cancelar</button>
            <button class="btn btn-danger btn-sm" @click="confirmarBaja" :disabled="bajando">
                <span v-if="bajando" class="spinner-border spinner-border-sm mr-1"></span>
                <i v-else class="bi bi-person-dash mr-1"></i>
                Confirmar
            </button>
        </div>
    </div>
<div v-else>
    <form @submit.prevent="reactivar">
        <button type="submit" class="btn btn-outline-success" :disabled="reactivando">
            <i class="bi bi-person-check mr-1"></i>Reactivar
        </button>
    </form>
</div>
            </div>

            <!-- Historial -->
            <div v-if="historial.length" class="mt-4">
                <h5>Historial</h5>
                <ul class="list-unstyled">
                    <li v-for="h in historial" :key="h.id" class="mb-1">
                        <span :class="h.accion === 'ALTA' ? 'badge badge-success' : 'badge badge-danger'">
                            {{ h.accion }}
                        </span>
                        {{ h.fecha }} — <small class="text-muted">{{ h.observaciones }}</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    socio:    { type: Object, required: true },
    historial: { type: Array, default: () => [] },
})

const form = useForm({ ...props.socio })
const mostrarConfirmBaja = ref(false)
const reactivando = ref(false)
const bajando = ref(false)

function enviar() {
    form.put(route('socios.update', props.socio.id))
}

function confirmarBaja() {
    bajando.value = true
    router.patch(route('socios.baja', props.socio.id), {}, {
        onFinish: () => { bajando.value = false },
    })
}

function reactivar() {
    reactivando.value = true
    router.patch(route('socios.reactivar', props.socio.id), {}, {
        onFinish: () => { reactivando.value = false },
    })
}
</script>

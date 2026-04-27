<template>
    <Head :title="`Editar: ${material.titulo}`" />
    <AppNavbar />

    <div class="container page-content">
        <div class="main-container" style="max-width: 760px; margin: auto;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="bi bi-pencil-square mr-2"></i>Editar material</h3>
                <Link v-if="qrUrl" :href="route('materiales.qr', material.id)" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-qr-code mr-1"></i>Ver QR
                </Link>
            </div>
            <FlashMessage />

            <div v-if="!material.id" class="alert alert-warning">Cargando datos del material…</div>
            <form v-else @submit.prevent="enviar">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Título <span class="text-danger">*</span></label>
                        <input v-model="form.titulo" type="text" class="form-control"
                               :class="{ 'is-invalid': form.errors.titulo }">
                        <div class="invalid-feedback">{{ form.errors.titulo }}</div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Año de publicación</label>
                        <input v-model.number="form.anio_publicacion" type="number" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Autor</label>
                        <input v-model="form.autor" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Editorial</label>
                        <input v-model="form.editorial" type="text" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Clasificación Dewey <span class="text-danger">*</span></label>
                        <select v-model.number="form.area_id" class="form-control">
                            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.codigo_dewey }} — {{ a.nombre }}</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Categoría física</label>
                        <select v-model="form.categoria" class="form-control">
                            <option value="">— Seleccioná —</option>
                            <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Disponibilidad</label>
                        <input v-model.number="form.disponibilidad" type="number" min="0" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Tipo de préstamo</label>
                        <select v-model="form.tipo_prestamo" class="form-control">
                            <option value="">— Sin especificar —</option>
                            <option>Solo consulta</option>
                            <option>Copia única</option>
                            <option>Transitorio</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Código</label>
                    <input type="text" class="form-control-plaintext font-weight-bold" :value="material.codigo" readonly>
                </div>

                <!-- Ubicación física -->
                <fieldset class="border p-3 mb-3">
                    <legend class="w-auto px-2 h6">Ubicación física (opcional)</legend>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Pasillo</label>
                            <input v-model="form.pasillo" type="text" class="form-control" maxlength="2" placeholder="A-Z">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tipo</label>
                            <select v-model="form.tipo_almacenamiento" class="form-control">
                                <option value="E">Estante (E)</option>
                                <option value="M">Mueble (M)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Estante (1-30)</label>
                            <input v-model.number="form.estante" type="number" min="1" max="30" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Nivel (1-6)</label>
                            <input v-model.number="form.nivel" type="number" min="1" max="6" class="form-control">
                        </div>
                    </div>
                    <div v-if="material.clasificacion_fisica" class="alert alert-light mb-0" style="font-size:.82rem;">
                        <i class="bi bi-geo-alt-fill mr-1 text-warning"></i>
                        Código actual: <strong>{{ material.clasificacion_fisica }}</strong>
                        — Se recalcula automáticamente al guardar.
                    </div>
                </fieldset>

                <div class="d-flex justify-content-between">
                    <div>
                        <Link :href="route('materiales.index')" class="btn btn-outline-secondary mr-2">Cancelar</Link>
                        <button type="button" class="btn btn-outline-danger" @click="eliminar">Eliminar</button>
                    </div>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    material:   { type: Object, required: true },
    areas:      { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
    qrUrl:      { type: String, default: null },
})

// Parsear clasificacion_fisica existente → ABREV-PASILLO-(TIPO)ESTANTE-NIVEL
function parsearUbicacion(cf) {
    if (!cf) return { pasillo: null, tipo: 'E', estante: null, nivel: null }
    const m = cf.match(/^[^-]+-([^-]+)-\(([^)]+)\)(\d+)-(\d+)$/)
    if (!m) return { pasillo: null, tipo: 'E', estante: null, nivel: null }
    return { pasillo: m[1], tipo: m[2], estante: parseInt(m[3]), nivel: parseInt(m[4]) }
}

const ubic = parsearUbicacion(props.material.clasificacion_fisica)

const form = useForm({
    titulo:               props.material.titulo          ?? '',
    autor:                props.material.autor           ?? '',
    anio_publicacion:     props.material.anio_publicacion,
    area_id:              props.material.area_id,
    categoria:            props.material.categoria       ?? '',
    disponibilidad:       props.material.disponibilidad,
    editorial:            props.material.editorial       ?? '',
    tipo_prestamo:        props.material.tipo_prestamo   ?? '',
    pasillo:              ubic.pasillo,
    tipo_almacenamiento:  ubic.tipo,
    estante:              ubic.estante,
    nivel:                ubic.nivel,
})

function enviar() {
    form.put(route('materiales.update', props.material.id))
}

function eliminar() {
    if (confirm(`¿Eliminar "${props.material.titulo}"?`)) {
        router.delete(route('materiales.destroy', props.material.id))
    }
}
</script>

<template>
    <Head title="Configuración" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Configuración del sistema</h4>
            <p class="text-muted small mb-0">Parámetros generales de la institución</p>
        </div>
    </div>

    <FlashMessage />

    <div class="row">
        <div class="col-lg-7">
            <form @submit.prevent="guardar" enctype="multipart/form-data">

                <!-- Institución -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent font-weight-bold" style="font-size:.88rem;">
                        <i class="bi bi-building mr-2"></i>Datos de la institución
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold small">Nombre de la institución</label>
                            <input v-model="form.nombre_institucion" type="text" class="form-control" maxlength="200">
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Logo institucional</label>
                            <div class="d-flex align-items-center" style="gap:1rem;">
                                <img v-if="previsualizacionLogo || config.logo_url"
                                     :src="previsualizacionLogo || config.logo_url"
                                     alt="Logo" height="56"
                                     class="border rounded p-1" style="max-width:120px;object-fit:contain;">
                                <div v-else class="border rounded d-flex align-items-center justify-content-center text-muted"
                                     style="width:80px;height:56px;font-size:.75rem;">Sin logo</div>
                                <div>
                                    <div class="custom-file" style="width:200px;">
                                        <input type="file" class="custom-file-input" id="logo"
                                               accept="image/jpg,image/jpeg,image/png,image/webp,image/svg+xml"
                                               @change="alSeleccionarLogo">
                                        <label class="custom-file-label" for="logo" style="font-size:.82rem;">
                                            {{ nombreLogo }}
                                        </label>
                                    </div>
                                    <small class="text-muted">JPG, PNG, WebP o SVG — máx. 1 MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Préstamos -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent font-weight-bold" style="font-size:.88rem;">
                        <i class="bi bi-calendar3 mr-2"></i>Parámetros de préstamos
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Días máximos de préstamo</label>
                                    <div class="d-flex align-items-center" style="gap:.5rem;">
                                        <input v-model.number="form.dias_prestamo" type="number" min="1" max="365"
                                               class="form-control" style="width:90px;">
                                        <span class="text-muted small">días</span>
                                    </div>
                                    <small class="text-muted">Límite máximo que puede seleccionarse al crear un préstamo.</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Días de alerta previa al vencimiento</label>
                                    <div class="d-flex align-items-center" style="gap:.5rem;">
                                        <input v-model.number="form.dias_alerta_previa" type="number" min="1" max="30"
                                               class="form-control" style="width:90px;">
                                        <span class="text-muted small">días</span>
                                    </div>
                                    <small class="text-muted">Se notifica cuando el vencimiento está dentro de este plazo.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="bi bi-check2 mr-1"></i>Guardar configuración
                    </button>
                </div>
            </form>
        </div>

        <!-- Info panel -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3"><i class="bi bi-info-circle mr-2 text-primary"></i>Información</h6>
                    <ul class="list-unstyled mb-0" style="font-size:.82rem;">
                        <li class="mb-2">
                            <i class="bi bi-dot text-primary"></i>
                            Los cambios de nombre se reflejan inmediatamente en toda la aplicación.
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-dot text-primary"></i>
                            Los días de préstamo se aplican como límite en la terminal de préstamos.
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-dot text-primary"></i>
                            Los días de alerta determinan con cuánta anticipación aparecen los avisos de vencimiento.
                        </li>
                        <li>
                            <i class="bi bi-dot text-primary"></i>
                            El logo se almacena en <code>storage/app/public/logos/</code>.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default { layout: AdminLayout }
</script>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    config: { type: Object, required: true },
})

const form = reactive({
    nombre_institucion: props.config.nombre_institucion ?? '',
    dias_prestamo:      parseInt(props.config.dias_prestamo ?? '14'),
    dias_alerta_previa: parseInt(props.config.dias_alerta_previa ?? '4'),
})

const archivoLogo    = ref(null)
const nombreLogo  = ref('Seleccioná una imagen...')
const previsualizacionLogo = ref(null)
const saving      = ref(false)

function alSeleccionarLogo(e) {
    const archivo = e.target.files[0]
    if (!archivo) return
    archivoLogo.value    = archivo
    nombreLogo.value  = archivo.name
    previsualizacionLogo.value = URL.createObjectURL(archivo)
}

function guardar() {
    saving.value = true
    const data = new FormData()
    data.append('_method', 'PUT')
    data.append('nombre_institucion', form.nombre_institucion)
    data.append('dias_prestamo',      String(form.dias_prestamo))
    data.append('dias_alerta_previa', String(form.dias_alerta_previa))
    if (archivoLogo.value) data.append('logo', archivoLogo.value)

    router.post(route('admin.configuracion.update'), data, {
        forceFormData: true,
        onFinish: () => { saving.value = false },
    })
}
</script>

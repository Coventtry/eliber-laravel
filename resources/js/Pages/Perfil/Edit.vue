<template>
    <Head title="Mi perfil" />

    <AppNavbar v-if="!esAdmin && !esAlumno" />
    <AppNavbarAlumno v-else-if="esAlumno" />

    <div :class="esAdmin ? '' : 'container page-content'">
        <FlashMessage v-if="!esAdmin" />

        <div class="row justify-content-center">
            <div class="col-md-7">
                <h4 class="mb-4">Mi perfil</h4>

                <div class="card shadow-sm mb-4">
                    <!-- Wallpaper banner -->
                    <div class="perfil-banner" :style="estiloFondo">
                        <label class="perfil-banner-edit" title="Cambiar portada">
                            <i class="bi bi-camera"></i>
                            <input type="file" class="d-none" accept="image/jpg,image/jpeg,image/png,image/webp"
                                   @change="alCambiarBanner">
                        </label>
                    </div>

                    <div class="card-body pt-0">
                        <!-- Avatar sobre el banner -->
                        <div class="perfil-avatar-wrap">
                            <img v-if="previsualizacionAvatar || perfil.picture_url"
                                 :src="previsualizacionAvatar || perfil.picture_url"
                                 class="perfil-avatar rounded-circle"
                                 alt="foto de perfil">
                            <div v-else class="perfil-avatar rounded-circle bg-light border d-flex align-items-center justify-content-center">
                                <i class="bi bi-person" style="font-size:2rem;color:#aaa;"></i>
                            </div>
                        </div>

                        <div class="mt-5 pt-2 mb-3">
                            <p class="mb-0 font-weight-bold">{{ perfil.nombre }}</p>
                            <p class="text-muted small mb-0">@{{ perfil.usuario }}</p>
                        </div>

                        <form @submit.prevent="guardar" enctype="multipart/form-data">

                            <div class="form-group">
                                <label class="font-weight-bold">Correo electrónico</label>
                                <input v-model="form.email" type="email" class="form-control"
                                       placeholder="tucorreo@ejemplo.com">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Apellido</label>
                                <input v-model="form.apellido" type="text" class="form-control"
                                       placeholder="Tu apellido">
                            </div>

                            <!-- Año y División (solo alumno, solo lectura) -->
                            <div v-if="es_alumno" class="form-row">
                                <div class="form-group col-6">
                                    <label class="font-weight-bold">Año</label>
                                    <div class="form-control-plaintext border rounded px-3 py-2 bg-light">
                                        {{ perfil.anio ? perfil.anio + '°' : '—' }}
                                    </div>
                                    <small class="text-muted">Solo puede modificarlo el bibliotecario.</small>
                                </div>
                                <div class="form-group col-6">
                                    <label class="font-weight-bold">División</label>
                                    <div class="form-control-plaintext border rounded px-3 py-2 bg-light">
                                        {{ perfil.division ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Foto de perfil</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="picture"
                                           accept="image/jpg,image/jpeg,image/png,image/webp"
                                           @change="alCambiarAvatar">
                                    <label class="custom-file-label" for="picture">
                                        {{ nombreAvatar }}
                                    </label>
                                </div>
                                <small class="text-muted">JPG, PNG o WebP — máx. 2 MB</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Imagen de portada</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="wallpaper"
                                           accept="image/jpg,image/jpeg,image/png,image/webp"
                                           @change="alCambiarBanner">
                                    <label class="custom-file-label" for="wallpaper">
                                        {{ nombreBanner }}
                                    </label>
                                </div>
                                <small class="text-muted">JPG, PNG o WebP — máx. 4 MB. Se muestra como banner en tu perfil.</small>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary" :disabled="guardando">
                                    <i class="bi bi-check2 mr-1"></i>Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <AppFooter v-if="!esAdmin" />
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'

export default {
    layout: (h, page) => {
        const roles = page.props.auth?.roles ?? []
        if (roles.includes('admin')) return h(AdminLayout, () => [page])
        return page
    },
}
</script>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppNavbarAlumno from '@/Components/AppNavbarAlumno.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    perfil:    { type: Object, required: true },
    es_alumno: { type: Boolean, default: false },
})

const page     = usePage()
const esAdmin  = computed(() => page.props.auth?.roles?.includes('admin') ?? false)
const esAlumno = computed(() => page.props.auth?.roles?.includes('alumno') ?? false)

const form = ref({
    email:    props.perfil.email    ?? '',
    apellido: props.perfil.apellido ?? '',
    anio:     props.perfil.anio     ?? '',
    division: props.perfil.division ?? '',
})
const archivoAvatar     = ref(null)
const archivoBanner  = ref(null)
const nombreAvatar   = ref('Seleccioná una imagen...')
const nombreBanner= ref('Seleccioná una imagen...')
const previsualizacionAvatar  = ref(null)
const previsualizacionBanner = ref(props.perfil.wallpaper_url ?? null)
const guardando      = ref(false)

const estiloFondo = computed(() => {
    const img = previsualizacionBanner.value
    return img
        ? `background-image:url('${img}');background-size:cover;background-position:center;`
        : ''
})

function alCambiarAvatar(e) {
    const archivo = e.target.files[0]
    if (!archivo) return
    archivoAvatar.value   = archivo
    nombreAvatar.value = archivo.name
    previsualizacionAvatar.value = URL.createObjectURL(archivo)
}

function alCambiarBanner(e) {
    const archivo = e.target.files[0]
    if (!archivo) return
    archivoBanner.value    = archivo
    nombreBanner.value  = archivo.name
    previsualizacionBanner.value = URL.createObjectURL(archivo)
}

function guardar() {
    guardando.value = true
    const data = new FormData()
    data.append('_method', 'PUT')
    if (form.value.email)    data.append('email',    form.value.email)
    if (form.value.apellido) data.append('apellido', form.value.apellido)
    if (archivoAvatar.value) data.append('picture',  archivoAvatar.value)
    if (archivoBanner.value) data.append('wallpaper', archivoBanner.value)

    router.post(route('perfil.update'), data, {
        forceFormData: true,
        onSuccess: () => { guardando.value = false; previsualizacionAvatar.value = null },
        onError:   () => { guardando.value = false },
    })
}
</script>

<style scoped>
.perfil-banner {
    position: relative;
    height: 140px;
    background: linear-gradient(135deg, var(--eliber-primary, #1a3a5c), #2d6a9f);
    border-radius: .375rem .375rem 0 0;
    overflow: hidden;
}

.perfil-banner-edit {
    position: absolute;
    bottom: .6rem;
    right: .7rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(0,0,0,.45);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: .9rem;
    transition: background .15s;
}
.perfil-banner-edit:hover { background: rgba(0,0,0,.65); }

.perfil-avatar-wrap {
    position: absolute;
    margin-top: -48px;
    margin-left: 1.5rem;
}
.perfil-avatar {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
</style>

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
                    <div class="perfil-banner" :style="bannerStyle">
                        <label class="perfil-banner-edit" title="Cambiar portada">
                            <i class="bi bi-camera"></i>
                            <input type="file" class="d-none" accept="image/jpg,image/jpeg,image/png,image/webp"
                                   @change="onWallpaper">
                        </label>
                    </div>

                    <div class="card-body pt-0">
                        <!-- Avatar sobre el banner -->
                        <div class="perfil-avatar-wrap">
                            <img v-if="avatarPreview || perfil.picture_url"
                                 :src="avatarPreview || perfil.picture_url"
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
                                <label class="font-weight-bold">Foto de perfil</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="picture"
                                           accept="image/jpg,image/jpeg,image/png,image/webp"
                                           @change="onAvatar">
                                    <label class="custom-file-label" for="picture">
                                        {{ avatarNombre }}
                                    </label>
                                </div>
                                <small class="text-muted">JPG, PNG o WebP — máx. 2 MB</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Imagen de portada</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="wallpaper"
                                           accept="image/jpg,image/jpeg,image/png,image/webp"
                                           @change="onWallpaper">
                                    <label class="custom-file-label" for="wallpaper">
                                        {{ wallpaperNombre }}
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
    perfil: { type: Object, required: true },
})

const page     = usePage()
const esAdmin  = computed(() => page.props.auth?.roles?.includes('admin') ?? false)
const esAlumno = computed(() => page.props.auth?.roles?.includes('alumno') ?? false)

const form           = ref({ email: props.perfil.email ?? '' })
const avatarFile     = ref(null)
const wallpaperFile  = ref(null)
const avatarNombre   = ref('Seleccioná una imagen...')
const wallpaperNombre= ref('Seleccioná una imagen...')
const avatarPreview  = ref(null)
const wallpaperPreview = ref(props.perfil.wallpaper_url ?? null)
const guardando      = ref(false)

const bannerStyle = computed(() => {
    const img = wallpaperPreview.value
    return img
        ? `background-image:url('${img}');background-size:cover;background-position:center;`
        : ''
})

function onAvatar(e) {
    const file = e.target.files[0]
    if (!file) return
    avatarFile.value   = file
    avatarNombre.value = file.name
    avatarPreview.value = URL.createObjectURL(file)
}

function onWallpaper(e) {
    const file = e.target.files[0]
    if (!file) return
    wallpaperFile.value    = file
    wallpaperNombre.value  = file.name
    wallpaperPreview.value = URL.createObjectURL(file)
}

function guardar() {
    guardando.value = true
    const data = new FormData()
    data.append('_method', 'PUT')
    if (form.value.email)   data.append('email', form.value.email)
    if (avatarFile.value)   data.append('picture', avatarFile.value)
    if (wallpaperFile.value)data.append('wallpaper', wallpaperFile.value)

    router.post(route('perfil.update'), data, {
        forceFormData: true,
        onSuccess: () => { guardando.value = false; avatarPreview.value = null },
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

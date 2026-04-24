<template>
    <Head title="Mi perfil" />

    <AppNavbar v-if="!esAdmin && !esAlumno" />
    <AppNavbarAlumno v-else-if="esAlumno" />

    <div :class="esAdmin ? '' : 'container page-content'">
        <FlashMessage v-if="!esAdmin" />

        <div class="row justify-content-center">
            <div class="col-md-6">
                <h4 class="mb-4">Mi perfil</h4>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Avatar actual -->
                        <div class="text-center mb-4">
                            <img
                                v-if="perfil.picture_url"
                                :src="perfil.picture_url"
                                alt="foto de perfil"
                                class="rounded-circle"
                                style="width:90px; height:90px; object-fit:cover;"
                            >
                            <div v-else class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center"
                                style="width:90px; height:90px;">
                                <i class="bi bi-person" style="font-size:2.5rem; color:#aaa;"></i>
                            </div>
                            <p class="text-muted small mt-2 mb-0">{{ perfil.nombre }} — <span class="font-weight-bold">{{ perfil.usuario }}</span></p>
                        </div>

                        <form @submit.prevent="guardar" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="font-weight-bold">Correo electrónico</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    placeholder="tucorreo@ejemplo.com"
                                >
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Foto de perfil</label>
                                <div class="custom-file">
                                    <input
                                        type="file"
                                        class="custom-file-input"
                                        id="picture"
                                        accept="image/jpg,image/jpeg,image/png,image/webp"
                                        @change="onFile"
                                    >
                                    <label class="custom-file-label" for="picture">
                                        {{ nombreArchivo }}
                                    </label>
                                </div>
                                <small class="text-muted">JPG, PNG o WebP — máx. 2 MB</small>
                            </div>

                            <!-- Preview -->
                            <div v-if="preview" class="text-center mb-3">
                                <img :src="preview" alt="preview" class="rounded-circle"
                                    style="width:70px; height:70px; object-fit:cover; border:2px solid var(--eliber-primary);">
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

const form          = ref({ email: props.perfil.email ?? '' })
const archivoFile   = ref(null)
const nombreArchivo = ref('Seleccioná una imagen...')
const preview       = ref(null)
const guardando     = ref(false)

function onFile(e) {
    const file = e.target.files[0]
    if (!file) return
    archivoFile.value   = file
    nombreArchivo.value = file.name
    preview.value = URL.createObjectURL(file)
}

function guardar() {
    guardando.value = true
    const data = new FormData()
    data.append('_method', 'PUT')
    if (form.value.email) data.append('email', form.value.email)
    if (archivoFile.value) data.append('picture', archivoFile.value)

    router.post(route('perfil.update'), data, {
        forceFormData: true,
        onSuccess: () => { guardando.value = false; preview.value = null },
        onError:   () => { guardando.value = false },
    })
}
</script>

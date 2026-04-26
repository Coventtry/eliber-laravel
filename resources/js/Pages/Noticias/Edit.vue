<template>
    <Head title="Editar noticia" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width: 600px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-newspaper mr-2"></i>Editar noticia</h3>
            <FlashMessage />
            <form @submit.prevent="enviar" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Título <span class="text-danger">*</span></label>
                    <input v-model="form.titulo" type="text" class="form-control"
                           :class="{ 'is-invalid': form.errors.titulo }">
                    <div class="invalid-feedback">{{ form.errors.titulo }}</div>
                </div>
                <div class="form-group">
                    <label>Descripción <span class="text-danger">*</span></label>
                    <textarea v-model="form.descripcion" rows="3" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Imagen</label>
                    <div v-if="noticia.imagen_url" class="mb-2">
                        <img :src="noticia.imagen_url" class="img-thumbnail" style="max-height: 120px;">
                        <small class="d-block text-muted">Seleccioná una nueva imagen para reemplazar.</small>
                    </div>
                    <input type="file" class="form-control-file" accept="image/*" @change="alSeleccionarImagen">
                    <img v-if="previsualizacion" :src="previsualizacion" class="mt-2 img-thumbnail" style="max-height: 120px;">
                </div>
                <div class="d-flex justify-content-between">
                    <Link :href="route('noticias.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <AppFooter />
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({ noticia: { type: Object, required: true } })
const previsualizacion = ref(null)
const form    = useForm({ titulo: props.noticia.titulo, descripcion: props.noticia.descripcion, imagen: null })

function alSeleccionarImagen(e) {
    const archivo = e.target.files[0]
    if (!archivo) return
    form.imagen  = archivo
    previsualizacion.value = URL.createObjectURL(archivo)
}

function enviar() {
    form.post(route('noticias.update', props.noticia.id), { _method: 'PUT' })
}
</script>

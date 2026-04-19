<template>
    <Head title="Nueva noticia" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container" style="max-width: 600px; margin: auto;">
            <h3 class="mb-4"><i class="bi bi-newspaper mr-2"></i>Nueva noticia</h3>
            <FlashMessage />
            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Título <span class="text-danger">*</span></label>
                    <input v-model="form.titulo" type="text" class="form-control"
                           :class="{ 'is-invalid': form.errors.titulo }">
                    <div class="invalid-feedback">{{ form.errors.titulo }}</div>
                </div>
                <div class="form-group">
                    <label>Descripción <span class="text-danger">*</span></label>
                    <textarea v-model="form.descripcion" rows="3" class="form-control"
                              :class="{ 'is-invalid': form.errors.descripcion }"></textarea>
                    <div class="invalid-feedback">{{ form.errors.descripcion }}</div>
                </div>
                <div class="form-group">
                    <label>Imagen</label>
                    <input type="file" class="form-control-file" accept="image/*" @change="handleFile">
                    <img v-if="preview" :src="preview" class="mt-2 img-thumbnail" style="max-height: 160px;">
                </div>
                <div class="d-flex justify-content-between">
                    <Link :href="route('noticias.index')" class="btn btn-outline-secondary">Cancelar</Link>
                    <button type="submit" class="btn btn-success" :disabled="form.processing">Publicar</button>
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

const preview = ref(null)
const form    = useForm({ titulo: '', descripcion: '', imagen: null })

function handleFile(e) {
    const file = e.target.files[0]
    if (!file) return
    form.imagen = file
    preview.value = URL.createObjectURL(file)
}

function submit() {
    form.post(route('noticias.store'))
}
</script>

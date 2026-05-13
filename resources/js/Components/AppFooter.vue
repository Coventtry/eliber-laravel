<template>
    <footer class="footer-eliber mt-auto">
        <div class="container">
            <div class="row footer-grid">
                <div class="col-md-4 footer-section">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><Link :href="route('dashboard')">Inicio</Link></li>
                        <li><Link :href="route('acerca')">Acerca del proyecto</Link></li>
                        <li><Link :href="route('faqs')">FAQs</Link></li>
                        <template v-if="footerLinks.length">
                            <li v-for="link in footerLinks" :key="link.id">
                                <a :href="link.url" target="_blank" rel="noopener">{{ link.label }}</a>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="col-md-4 footer-section">
                    <h4>Repositorio</h4>
                    <ul>
                        <li><a href="https://github.com/Coventtry/eliber-laravel" target="_blank">GitHub del proyecto</a></li>
                    </ul>
                    <template v-if="auth">
                        <h4 class="mt-3">Sugerencias</h4>
                        <ul>
                            <li>
                                <button class="btn btn-link p-0 footer-link" @click="abrirNota">
                                    <i class="bi bi-pencil-square mr-1"></i>Dejar una nota
                                </button>
                            </li>
                        </ul>
                    </template>
                </div>
                <div class="col-md-4 footer-section text-center text-md-right">
                    <div class="tech-icons-wrapper">
                        <img src="/img/devicon/html5.svg" alt="HTML5" width="32" height="32">
                        <img src="/img/devicon/css3.svg" alt="CSS3" width="32" height="32">
                        <img src="/img/devicon/bootstrap.svg" alt="Bootstrap" width="32" height="32">
                        <img src="/img/devicon/php.svg" alt="PHP" width="32" height="32">
                        <img src="/img/devicon/vuejs.svg" alt="Vue.js" width="32" height="32">
                        <img src="/img/devicon/laravel.svg" alt="Laravel" width="32" height="32">
                    </div>
                    <div class="social-icons">
                        <a href="https://github.com/Coventtry" target="_blank" aria-label="GitHub">
                            <i class="bi bi-github"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center">
                &copy; {{ year }} E-liber — Sistema de Gestión de Biblioteca. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Modal: Nueva nota -->
    <div v-if="modalNota" class="modal d-block modal-backdrop-custom" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-nota-title" @click.self="cerrarNota">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0" id="modal-nota-title">
                        <i class="bi bi-pencil-square mr-2"></i>Nueva nota / sugerencia
                    </h6>
                    <button type="button" class="close" @click="cerrarNota" aria-label="Cerrar"><span>&times;</span></button>
                </div>
                <form @submit.prevent="guardarNota">
                    <div class="modal-body pb-2">
                        <textarea
                            v-model="textoNota"
                            class="form-control"
                            rows="4"
                            maxlength="2000"
                            placeholder="Escribí tu sugerencia, error detectado u observación..."
                            autofocus
                            required
                        ></textarea>
                        <small class="text-muted">{{ textoNota.length }}/2000</small>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" @click="cerrarNota">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-primary" :disabled="!textoNota.trim() || guardando">
                            <i class="bi bi-check2 mr-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const year        = new Date().getFullYear()
const page        = usePage()
const auth        = computed(() => page.props.auth?.user ?? null)
const footerLinks = computed(() => page.props.footer_links ?? [])

const modalNota = ref(false)
const textoNota = ref('')
const guardando = ref(false)

function abrirNota() {
    textoNota.value = ''
    modalNota.value = true
}

function cerrarNota() {
    modalNota.value = false
    textoNota.value = ''
    guardando.value = false
}

function guardarNota() {
    if (!textoNota.value.trim()) return
    guardando.value = true
    router.post(
        route('anotaciones.store'),
        { anotacion: textoNota.value, from_modal: true },
        { onSuccess: cerrarNota, onError: () => { guardando.value = false } }
    )
}
</script>

<style scoped>
.modal-backdrop-custom {
    background: rgba(0,0,0,.45);
}
</style>
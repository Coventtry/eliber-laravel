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
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/html5/html5-original.svg" alt="HTML5" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/css3/css3-original.svg" alt="CSS3" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/bootstrap/bootstrap-original.svg" alt="Bootstrap" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" alt="PHP" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/vuejs/vuejs-original.svg" alt="Vue.js" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/laravel/laravel-original.svg" alt="Laravel" width="32" height="32">
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
    <div v-if="modalNota" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.45);" @click.self="cerrarNota">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0">
                        <i class="bi bi-pencil-square mr-2"></i>Nueva nota / sugerencia
                    </h6>
                    <button type="button" class="close" @click="cerrarNota"><span>&times;</span></button>
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
                        <button type="submit" class="btn btn-sm btn-primary" :disabled="!textoNota.trim() || guardando || cooldown > 0">
                            <span v-if="cooldown > 0"><i class="bi bi-hourglass-split mr-1"></i>Esperá {{ cooldown }}s…</span>
                            <span v-else><i class="bi bi-check2 mr-1"></i>Guardar</span>
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
const cooldown  = ref(0)

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
    if (!textoNota.value.trim() || cooldown.value > 0) return
    guardando.value = true
    router.post(
        route('feedback.nota'),
        { anotacion: textoNota.value },
        {
            onSuccess: () => {
                cerrarNota()
                cooldown.value = 5
                const t = setInterval(() => { if (--cooldown.value <= 0) clearInterval(t) }, 1000)
            },
            onError: () => { guardando.value = false },
        }
    )
}
</script>
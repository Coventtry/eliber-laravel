<template>
    <Head title="Gestión de Contenido" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Gestión de Contenido</h4>
            <p class="text-muted small mb-0">FAQs, anuncios y enlaces del footer</p>
        </div>
    </div>

    <FlashMessage />

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'faqs' }" @click="tab = 'faqs'">
                <i class="bi bi-question-circle mr-1"></i>FAQs
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'anuncio' }" @click="tab = 'anuncio'">
                <i class="bi bi-megaphone mr-1"></i>Anuncio
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'footer' }" @click="tab = 'footer'">
                <i class="bi bi-link-45deg mr-1"></i>Footer
            </button>
        </li>
    </ul>

    <!-- ── TAB: FAQs ────────────────────────────────────────────────────────── -->
    <div v-if="tab === 'faqs'">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Preguntas frecuentes</h6>
            <button class="btn btn-primary btn-sm" @click="openCreateFaq">
                <i class="bi bi-plus mr-1"></i>Nueva pregunta
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush">
                <div
                    v-for="faq in faqs"
                    :key="faq.id"
                    class="list-group-item"
                >
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 mr-3">
                            <p class="mb-1 font-weight-bold" style="font-size:.88rem;">
                                <span v-if="!faq.activa" class="badge badge-secondary mr-1" style="font-size:.65rem;">Inactiva</span>
                                {{ faq.pregunta }}
                            </p>
                            <p class="text-muted mb-0 small">{{ faq.respuesta }}</p>
                        </div>
                        <div class="d-flex" style="gap:.4rem;flex-shrink:0;">
                            <button class="btn btn-sm btn-outline-secondary" @click="openEditFaq(faq)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteFaq(faq)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="!faqs.length" class="list-group-item text-center text-muted py-4">
                    Sin preguntas. Crea la primera.
                </div>
            </div>
        </div>
    </div>

    <!-- ── TAB: Anuncio ──────────────────────────────────────────────────────── -->
    <div v-if="tab === 'anuncio'">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Banner de anuncio global</h6>

                <!-- Preview -->
                <div v-if="anuncioForm.texto" :class="`alert alert-${anuncioForm.estilo} mb-3 py-2`"
                     style="font-size:.85rem;">
                    <i class="bi bi-megaphone mr-2"></i>{{ anuncioForm.texto }}
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">Texto del anuncio</label>
                    <input v-model="anuncioForm.texto" type="text" class="form-control" maxlength="500"
                        placeholder="Ej: Mantenimiento programado esta noche a las 22hs.">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">Estilo</label>
                    <div class="d-flex" style="gap:.5rem;flex-wrap:wrap;">
                        <button
                            v-for="estilo in estilos"
                            :key="estilo.key"
                            type="button"
                            class="btn btn-sm"
                            :class="[`btn-${estilo.key}`, anuncioForm.estilo === estilo.key ? '' : 'btn-outline-secondary']"
                            :style="anuncioForm.estilo === estilo.key ? '' : 'opacity:.6;'"
                            @click="anuncioForm.estilo = estilo.key"
                        >{{ estilo.label }}</button>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input v-model="anuncioForm.activo" type="checkbox" class="custom-control-input" id="anuncioActivo">
                        <label class="custom-control-label" for="anuncioActivo">Anuncio activo (visible en la navbar)</label>
                    </div>
                </div>

                <button class="btn btn-primary btn-sm" :disabled="savingAnuncio" @click="guardarAnuncio">
                    <span v-if="savingAnuncio" class="spinner-border spinner-border-sm mr-1"></span>
                    <i v-else class="bi bi-check2 mr-1"></i>Guardar anuncio
                </button>
            </div>
        </div>
    </div>

    <!-- ── TAB: Footer ──────────────────────────────────────────────────────── -->
    <div v-if="tab === 'footer'">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Enlaces del footer</h6>
            <button class="btn btn-primary btn-sm" @click="openCreateLink">
                <i class="bi bi-plus mr-1"></i>Nuevo enlace
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush">
                <div v-for="link in footerLinks" :key="link.id" class="list-group-item d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-weight-bold small">{{ link.label }}</span>
                        <span class="text-muted small ml-2">{{ link.url }}</span>
                    </div>
                    <div class="d-flex" style="gap:.4rem;">
                        <button class="btn btn-sm btn-outline-secondary" @click="openEditLink(link)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" @click="deleteLink(link)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div v-if="!footerLinks.length" class="list-group-item text-center text-muted py-4">
                    Sin enlaces. Agrega el primero.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal FAQ -->
    <div v-if="faqModal.show" class="modal-backdrop-custom" @click.self="faqModal.show = false">
        <div class="modal-dialog-custom" style="max-width:580px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ faqModal.faq ? 'Editar pregunta' : 'Nueva pregunta' }}</h5>
                    <button type="button" class="close" @click="faqModal.show = false"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Pregunta <span class="text-danger">*</span></label>
                        <input v-model="faqForm.pregunta" type="text" class="form-control form-control-sm" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Respuesta <span class="text-danger">*</span></label>
                        <textarea v-model="faqForm.respuesta" class="form-control form-control-sm" rows="4" maxlength="3000"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Orden</label>
                        <input v-model.number="faqForm.orden" type="number" min="0" class="form-control form-control-sm" style="width:80px;">
                    </div>
                    <div v-if="faqModal.faq" class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input v-model="faqForm.activa" type="checkbox" class="custom-control-input" id="faqActiva">
                            <label class="custom-control-label" for="faqActiva">Activa (visible en /faqs)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" @click="faqModal.show = false">Cancelar</button>
                    <button class="btn btn-primary btn-sm" :disabled="faqModal.saving" @click="guardarFaq">
                        <span v-if="faqModal.saving" class="spinner-border spinner-border-sm mr-1"></span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Footer link -->
    <div v-if="linkModal.show" class="modal-backdrop-custom" @click.self="linkModal.show = false">
        <div class="modal-dialog-custom" style="max-width:480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ linkModal.link ? 'Editar enlace' : 'Nuevo enlace' }}</h5>
                    <button type="button" class="close" @click="linkModal.show = false"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Texto <span class="text-danger">*</span></label>
                        <input v-model="linkForm.label" type="text" class="form-control form-control-sm" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">URL <span class="text-danger">*</span></label>
                        <input v-model="linkForm.url" type="text" class="form-control form-control-sm" maxlength="500" placeholder="/politica-privacidad">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Orden</label>
                        <input v-model.number="linkForm.orden" type="number" min="0" class="form-control form-control-sm" style="width:80px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" @click="linkModal.show = false">Cancelar</button>
                    <button class="btn btn-primary btn-sm" :disabled="linkModal.saving" @click="guardarLink">
                        <span v-if="linkModal.saving" class="spinner-border spinner-border-sm mr-1"></span>
                        Guardar
                    </button>
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
import { ref, reactive } from 'vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    faqs:        { type: Array, default: () => [] },
    footerLinks: { type: Array, default: () => [] },
    anuncio:     { type: Object, default: () => ({ texto: '', estilo: 'info', activo: false }) },
})

const tab = ref('faqs')
const estilos = [
    { key: 'info',    label: 'Info' },
    { key: 'success', label: 'Éxito' },
    { key: 'warning', label: 'Advertencia' },
    { key: 'danger',  label: 'Peligro' },
]

// ── Anuncio ───────────────────────────────────────────────────────────────────
const anuncioForm  = reactive({ ...props.anuncio })
const savingAnuncio = ref(false)

function guardarAnuncio() {
    savingAnuncio.value = true
    router.patch(route('admin.contenido.anuncio.update'), anuncioForm, {
        preserveScroll: true,
        onFinish: () => { savingAnuncio.value = false },
    })
}

// ── FAQs ──────────────────────────────────────────────────────────────────────
const faqModal = reactive({ show: false, faq: null, saving: false })
const faqForm  = reactive({ pregunta: '', respuesta: '', orden: 0, activa: true })

function openCreateFaq() {
    faqModal.faq = null
    Object.assign(faqForm, { pregunta: '', respuesta: '', orden: 0, activa: true })
    faqModal.show = true
}
function openEditFaq(faq) {
    faqModal.faq = faq
    Object.assign(faqForm, { pregunta: faq.pregunta, respuesta: faq.respuesta, orden: faq.orden, activa: faq.activa })
    faqModal.show = true
}
function guardarFaq() {
    faqModal.saving = true
    const url    = faqModal.faq ? route('admin.contenido.faqs.update', faqModal.faq.id) : route('admin.contenido.faqs.store')
    const method = faqModal.faq ? 'put' : 'post'
    router[method](url, faqForm, {
        preserveScroll: true,
        onSuccess: () => { faqModal.show = false; faqModal.saving = false },
        onError:   () => { faqModal.saving = false },
    })
}
function deleteFaq(faq) {
    if (confirm(`¿Eliminás la pregunta "${faq.pregunta}"?`)) {
        router.delete(route('admin.contenido.faqs.destroy', faq.id), { preserveScroll: true })
    }
}

// ── Footer links ──────────────────────────────────────────────────────────────
const linkModal = reactive({ show: false, link: null, saving: false })
const linkForm  = reactive({ label: '', url: '', orden: 0 })

function openCreateLink() {
    linkModal.link = null
    Object.assign(linkForm, { label: '', url: '', orden: 0 })
    linkModal.show = true
}
function openEditLink(link) {
    linkModal.link = link
    Object.assign(linkForm, { label: link.label, url: link.url, orden: link.orden })
    linkModal.show = true
}
function guardarLink() {
    linkModal.saving = true
    const url    = linkModal.link ? route('admin.contenido.footer.update', linkModal.link.id) : route('admin.contenido.footer.store')
    const method = linkModal.link ? 'put' : 'post'
    router[method](url, linkForm, {
        preserveScroll: true,
        onSuccess: () => { linkModal.show = false; linkModal.saving = false },
        onError:   () => { linkModal.saving = false },
    })
}
function deleteLink(link) {
    if (confirm(`¿Eliminás el enlace "${link.label}"?`)) {
        router.delete(route('admin.contenido.footer.destroy', link.id), { preserveScroll: true })
    }
}
</script>

<style scoped>
.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-dialog-custom {
    width: 100%;
    max-width: 560px;
}
</style>

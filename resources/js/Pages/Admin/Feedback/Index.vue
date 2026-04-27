<template>
    <Head title="Feedback" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Feedback &amp; Notas</h4>
            <p class="text-muted small mb-0">Seguimiento de sugerencias y solicitudes del sistema</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" @click="mostrarConfiguracion = true">
                <i class="bi bi-gear mr-1"></i>Configuración
            </button>
            <button class="btn btn-primary btn-sm" @click="abrirCrear">
                <i class="bi bi-plus mr-1"></i>Nueva tarjeta
            </button>
        </div>
    </div>

    <FlashMessage />

    <!-- Leyenda de prioridades -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:.75rem;">
            <span class="text-muted small mr-1">Prioridad:</span>
            <div v-for="(color, key) in settings.alertColors" :key="key" class="d-flex align-items-center" style="gap:.3rem;">
                <div class="rounded-circle" :style="`width:10px;height:10px;background:${color};flex-shrink:0;`"></div>
                <span class="small text-capitalize">{{ prioridadLabels[key] }}</span>
            </div>
            <div class="mx-2" style="width:1px;height:16px;background:#dee2e6;"></div>
            <span class="text-muted small mr-1">Roles:</span>
            <span v-for="(cls, rol) in rolColors" :key="rol" :class="['badge', cls, 'small']">{{ rol }}</span>
        </div>
    </div>

    <!-- Tablero Kanban -->
    <div class="kanban-board">
        <div
            v-for="col in columns"
            :key="col.id"
            class="kanban-column"
            :class="{ 'kanban-over': dragOver === col.id }"
            @dragover.prevent="dragOver = col.id"
            @dragleave="dragOver = null"
            @drop="onDrop($event, col.id)"
        >
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 font-weight-bold" style="font-size:.88rem;">{{ col.label }}</h6>
                <span class="badge badge-secondary">{{ columnCards(col.id).length }}</span>
            </div>

            <div class="kanban-cards-area">
                <div
                    v-for="card in columnCards(col.id)"
                    :key="card.id"
                    class="kanban-card"
                    draggable="true"
                    @dragstart="onDragStart($event, card)"
                    @dragend="dragOver = null"
                >
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0 flex-grow-1 pr-2" style="font-size:.82rem;line-height:1.35;">{{ card.titulo }}</h6>
                        <div
                            class="rounded-circle flex-shrink-0 mt-1"
                            :style="`width:8px;height:8px;background:${settings.alertColors[card.prioridad]};`"
                            :title="prioridadLabels[card.prioridad]"
                        ></div>
                    </div>

                    <p v-if="card.descripcion" class="text-muted small mb-2"
                       style="font-size:.78rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ card.descripcion }}
                    </p>

                    <div class="d-flex flex-wrap mb-2" style="gap:.25rem;">
                        <span
                            v-for="tag in card.tags"
                            :key="tag"
                            class="badge kanban-tag"
                        >{{ tag }}</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted" style="font-size:.72rem;">
                                {{ card.creado_por?.nombre ?? '—' }}
                            </span>
                            <span
                                v-if="card.creado_por?.institucion && !card.creado_por?.roles?.includes('admin')"
                                class="d-block text-muted"
                                style="font-size:.68rem;"
                            >
                                <i class="bi bi-building mr-1"></i>{{ card.creado_por.institucion }}
                            </span>
                        </div>
                        <span class="small text-muted" style="font-size:.7rem;">{{ card.created_at }}</span>
                    </div>

                    <!-- Acciones -->
                    <div class="d-flex justify-content-end mt-2" style="gap:.25rem;">
                        <button class="btn btn-link btn-sm p-0 text-muted" @click="abrirEditar(card)" title="Editar">
                            <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                        </button>
                        <button class="btn btn-link btn-sm p-0 text-danger" @click="confirmarEliminar(card)" title="Eliminar">
                            <i class="bi bi-trash" style="font-size:.8rem;"></i>
                        </button>
                    </div>
                </div>

                <div v-if="columnCards(col.id).length === 0" class="kanban-empty">
                    Sin tarjetas
                </div>
            </div>
        </div>
    </div>

    <!-- Modal crear/editar -->
    <div v-if="mostrarModal" class="modal-backdrop-custom" @click.self="mostrarModal = false">
        <div class="modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ tarjetaEdicion ? 'Editar tarjeta' : 'Nueva tarjeta' }}</h5>
                    <button type="button" class="close" @click="mostrarModal = false"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Título <span class="text-danger">*</span></label>
                        <input v-model="form.titulo" type="text" class="form-control form-control-sm" maxlength="255">
                        <div v-if="errors.titulo" class="text-danger small">{{ errors.titulo }}</div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Descripción</label>
                        <textarea v-model="form.descripcion" class="form-control form-control-sm" rows="3" maxlength="2000"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Prioridad</label>
                        <select v-model="form.prioridad" class="form-control form-control-sm">
                            <option v-for="(label, key) in prioridadLabels" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tags</label>
                        <div class="d-flex flex-wrap mb-2" style="gap:.35rem;">
                            <button
                                v-for="tag in settings.availableTags"
                                :key="tag"
                                type="button"
                                class="btn btn-sm"
                                :class="form.tags.includes(tag) ? 'btn-primary' : 'btn-outline-secondary'"
                                style="font-size:.72rem;padding:.15rem .5rem;"
                                @click="toggleTag(tag)"
                            >{{ tag }}</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="mostrarModal = false">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm" :disabled="saving" @click="guardar">
                        <span v-if="saving" class="spinner-border spinner-border-sm mr-1"></span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal configuración -->
    <div v-if="mostrarConfiguracion" class="modal-backdrop-custom" @click.self="mostrarConfiguracion = false">
        <div class="modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear mr-2"></i>Configuración del Kanban</h5>
                    <button type="button" class="close" @click="mostrarConfiguracion = false"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Tiempo desaparición -->
                    <div class="form-group">
                        <label class="font-weight-bold small">Días hasta desaparecer en Publicado</label>
                        <div class="d-flex align-items-center" style="gap:.5rem;">
                            <input v-model.number="settingsForm.disappearTime" type="number" min="1" max="365" class="form-control form-control-sm" style="width:80px;">
                            <span class="text-muted small">días</span>
                        </div>
                        <small class="text-muted">Las tarjetas en Publicado desaparecen pasados estos días</small>
                    </div>

                    <!-- Colores de prioridad -->
                    <div class="form-group">
                        <label class="font-weight-bold small">Colores de prioridad</label>
                        <div class="row">
                            <div v-for="(color, key) in settingsForm.alertColors" :key="key" class="col-6 mb-2">
                                <label class="small text-capitalize mb-1">{{ prioridadLabels[key] }}</label>
                                <div class="d-flex align-items-center" style="gap:.4rem;">
                                    <input type="color" v-model="settingsForm.alertColors[key]"
                                        class="border rounded" style="width:36px;height:30px;cursor:pointer;padding:1px;">
                                    <input type="text" v-model="settingsForm.alertColors[key]"
                                        class="form-control form-control-sm font-weight-bold" style="font-family:monospace;width:90px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tags disponibles -->
                    <div class="form-group">
                        <label class="font-weight-bold small">Tags disponibles</label>
                        <div class="d-flex flex-wrap mb-2" style="gap:.35rem;">
                            <span
                                v-for="tag in settingsForm.availableTags"
                                :key="tag"
                                class="badge badge-light border d-flex align-items-center"
                                style="font-size:.75rem;gap:.3rem;"
                            >
                                {{ tag }}
                                <button type="button" class="btn btn-link btn-sm p-0 text-danger ml-1"
                                    style="font-size:.7rem;line-height:1;" @click="removeTag(tag)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                        </div>
                        <div class="d-flex" style="gap:.4rem;">
                            <input v-model="newTag" type="text" class="form-control form-control-sm" placeholder="Nuevo tag..."
                                @keydown.enter.prevent="addTag" maxlength="50">
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addTag">Agregar</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="mostrarConfiguracion = false">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="guardarConfiguracion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal confirmación eliminar -->
    <div v-if="objetoEliminar" class="modal-backdrop-custom" @click.self="objetoEliminar = null">
        <div class="modal-dialog-custom" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-trash mr-2"></i>Eliminar tarjeta</h5>
                    <button type="button" class="close" @click="objetoEliminar = null"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>¿Eliminás la tarjeta <strong>{{ objetoEliminar.titulo }}</strong>? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="objetoEliminar = null">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" @click="ejecutarEliminar">Eliminar</button>
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
import { ref, reactive, computed } from 'vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const props = defineProps({
    cards: { type: Array, default: () => [] },
})

// ── Constantes ──────────────────────────────────────────────────────────────
const prioridadLabels = { low: 'Baja', medium: 'Media', high: 'Alta', urgent: 'Urgente' }
const rolColors       = { admin: 'badge-primary', bibliotecario: 'badge-info', alumno: 'badge-success' }

const columns = [
    { id: 'backlog',     label: 'Backlog' },
    { id: 'in_progress', label: 'En progreso' },
    { id: 'completed',   label: 'Completado' },
    { id: 'published',   label: 'Publicado' },
]

// ── Configuración ────────────────────────────────────────────────────────────
const settings = reactive({
    disappearTime: 7,
    alertColors: { low: '#10b981', medium: '#f59e0b', high: '#ef4444', urgent: '#dc2626' },
    availableTags: ['Bug', 'Mejora', 'Feature', 'Diseño', 'Seguridad', 'UX', 'Documentación', 'Performance'],
})
const settingsForm = reactive({ ...JSON.parse(JSON.stringify(settings)) })
const mostrarConfiguracion = ref(false)
const newTag        = ref('')

function guardarConfiguracion() {
    Object.assign(settings, JSON.parse(JSON.stringify(settingsForm)))
    mostrarConfiguracion.value = false
}
function addTag() {
    const t = newTag.value.trim()
    if (t && !settingsForm.availableTags.includes(t)) settingsForm.availableTags.push(t)
    newTag.value = ''
}
function removeTag(tag) {
    settingsForm.availableTags = settingsForm.availableTags.filter(t => t !== tag)
}

// ── Columnas ─────────────────────────────────────────────────────────────────
function columnCards(colId) {
    let list = props.cards.filter(c => c.columna === colId)
    if (colId === 'published') {
        const cutoff = new Date()
        cutoff.setDate(cutoff.getDate() - settings.disappearTime)
        list = list.filter(c => new Date(c.created_at) >= cutoff)
    }
    return list
}

// ── Drag & Drop ───────────────────────────────────────────────────────────────
const dragOver   = ref(null)
const draggingId = ref(null)

function onDragStart(event, card) {
    draggingId.value = card.id
    event.dataTransfer.effectAllowed = 'move'
}
function onDrop(event, colId) {
    dragOver.value = null
    if (!draggingId.value) return
    const card = props.cards.find(c => c.id === draggingId.value)
    if (!card || card.columna === colId) return
    router.patch(route('admin.feedback.mover', draggingId.value), { columna: colId }, {
        preserveScroll: true,
    })
    draggingId.value = null
}

// ── Modal crear/editar ────────────────────────────────────────────────────────
const mostrarModal = ref(false)
const tarjetaEdicion  = ref(null)
const saving    = ref(false)
const errors    = ref({})
const form      = reactive({ titulo: '', descripcion: '', prioridad: 'medium', tags: [] })

function abrirCrear() {
    tarjetaEdicion.value = null
    Object.assign(form, { titulo: '', descripcion: '', prioridad: 'medium', tags: [] })
    errors.value   = {}
    mostrarModal.value = true
}
function abrirEditar(card) {
    tarjetaEdicion.value = card
    Object.assign(form, {
        titulo:      card.titulo,
        descripcion: card.descripcion ?? '',
        prioridad:   card.prioridad,
        tags:        [...(card.tags ?? [])],
    })
    errors.value   = {}
    mostrarModal.value = true
}
function toggleTag(tag) {
    const idx = form.tags.indexOf(tag)
    if (idx === -1) form.tags.push(tag)
    else form.tags.splice(idx, 1)
}
function guardar() {
    saving.value = true
    errors.value = {}
    const data = { titulo: form.titulo, descripcion: form.descripcion, prioridad: form.prioridad, tags: form.tags }
    const url  = tarjetaEdicion.value
        ? route('admin.feedback.update', tarjetaEdicion.value.id)
        : route('admin.feedback.store')
    const method = tarjetaEdicion.value ? 'put' : 'post'

    router[method](url, data, {
        preserveScroll: true,
        onSuccess: () => { saving.value = false; mostrarModal.value = false },
        onError: (e)  => { saving.value = false; errors.value = e },
    })
}

// ── Eliminar ──────────────────────────────────────────────────────────────────
const objetoEliminar = ref(null)
function confirmarEliminar(card) { objetoEliminar.value = card }
function ejecutarEliminar() {
    router.delete(route('admin.feedback.destroy', objetoEliminar.value.id), {
        preserveScroll: true,
        onSuccess: () => { objetoEliminar.value = null },
    })
}
</script>

<style scoped>
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: .5rem;
    align-items: flex-start;
}

.kanban-column {
    flex: 1;
    min-width: 240px;
    background: #f0f2f5;
    border-radius: 8px;
    padding: 1rem;
    transition: background .2s;
}
.kanban-column.kanban-over {
    background: #e8f0fe;
}

.kanban-cards-area {
    display: flex;
    flex-direction: column;
    gap: .6rem;
    min-height: 80px;
}

.kanban-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: .75rem;
    cursor: grab;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    transition: box-shadow .15s, opacity .15s;
}
.kanban-card:active { cursor: grabbing; opacity: .8; }
.kanban-card:hover  { box-shadow: 0 3px 8px rgba(0,0,0,.1); }

.kanban-empty {
    text-align: center;
    color: #adb5bd;
    font-size: .78rem;
    padding: 1rem 0;
}

.kanban-tag {
    background: #eef2ff;
    color: #4f46e5;
    font-size: .65rem;
    font-weight: 500;
}

/* Modales */
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

<template>
    <div v-if="show" class="modal d-block modal-backdrop-custom"
         tabindex="-1"
         role="dialog" aria-modal="true"
         :aria-labelledby="modalId + '-title'"
         @click.self="handleBackdrop">
        <div class="modal-dialog" :class="[sizeClass, centered ? 'modal-dialog-centered' : '']">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" :id="modalId + '-title'">
                        <slot name="title">{{ title }}</slot>
                    </h5>
                    <button type="button" class="close" @click="close" :disabled="noClose" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <slot />
                </div>
                <div v-if="$slots.footer" class="modal-footer">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    show:     { type: Boolean, required: true },
    title:    { type: String, default: '' },
    size:     { type: String, default: '' },
    centered: { type: Boolean, default: true },
    noClose:  { type: Boolean, default: false },
    backdrop: { type: Boolean, default: true },
    modalId:  { type: String, default: '' },
})

const emit = defineEmits(['close'])

const uid = ref('modal-' + Math.random().toString(36).slice(2, 8))
const modalId = computed(() => props.modalId || uid.value)

const sizeClass = computed(() => {
    if (!props.size) return ''
    return `modal-${props.size}`
})

function close() {
    if (!props.noClose) emit('close')
}

function handleBackdrop() {
    if (props.backdrop) close()
}

watch(() => props.show, (showing) => {
    if (showing) {
        document.addEventListener('keydown', onKeydown)
    } else {
        document.removeEventListener('keydown', onKeydown)
    }
}, { immediate: false })

function onKeydown(e) {
    if (e.key === 'Escape' && !props.noClose) {
        close()
    }
}
</script>

<style scoped>
.modal-dialog {
    max-width: 95vw;
    margin: 1.75rem auto;
}
@media (min-width: 576px) {
    .modal-dialog { max-width: 500px; }
}
.modal-dialog.modal-lg { max-width: 95vw; }
@media (min-width: 992px) {
    .modal-dialog.modal-lg { max-width: 800px; }
}
</style>
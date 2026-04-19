<template>
    <div class="modal fade" :id="modalId" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">{{ message }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" @click="confirm">{{ confirmText }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    modalId:     { type: String, required: true },
    title:       { type: String, default: 'Confirmar acción' },
    message:     { type: String, default: '¿Estás seguro?' },
    confirmText: { type: String, default: 'Confirmar' },
})

const emit = defineEmits(['confirmed'])

function confirm() {
    // Cerrar modal via jQuery (Bootstrap 4 requiere jQuery)
    window.$(`#${props.modalId}`).modal('hide')
    emit('confirmed')
}
</script>

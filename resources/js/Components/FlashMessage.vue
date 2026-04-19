<template>
    <div v-if="message" :class="`alert alert-${type} flash-message`" role="alert">
        {{ message }}
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const message = computed(() => page.props.flash?.success || page.props.flash?.error || null)
const type = computed(() => page.props.flash?.success ? 'success' : 'danger')
</script>

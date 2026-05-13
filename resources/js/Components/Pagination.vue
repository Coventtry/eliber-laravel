<template>
    <div v-if="links.length > 3" class="d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li v-for="(link, i) in links" :key="i"
                    class="page-item"
                    :class="{ disabled: !link.url, active: link.active }">
                    <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label" />
                    <span v-else class="page-link" v-html="link.label" />
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: { type: Array, required: true },
})
</script>

<style scoped>
.pagination .page-link {
    min-width: 38px;
    text-align: center;
}
@media (max-width: 575.98px) {
    .pagination .page-link {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
    }
}
</style>

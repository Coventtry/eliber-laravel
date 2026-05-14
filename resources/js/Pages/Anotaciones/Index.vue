<template>
    <Head title="Notas" />
    <AppNavbar />
    <div class="container page-content">
        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-journal-text mr-2"></i>Notas</h3>
                <Link :href="route('anotaciones.create')" class="btn btn-success">
                    <i class="bi bi-plus-lg mr-1"></i>Nueva
                </Link>
            </div>
            <FlashMessage />
            <ul class="list-group">
                <li v-for="a in anotaciones.data" :key="a.id" class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span>{{ a.anotacion }}</span>
                        <small class="text-muted ml-3 text-nowrap">{{ a.fecha }}</small>
                    </div>
                </li>
                <li v-if="!anotaciones.data.length" class="list-group-item text-center text-muted">
                    No hay notas.
                </li>
            </ul>
            <Pagination :links="anotaciones.links" class="mt-3" />
        </div>
    </div>
    <AppFooter />
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'
defineProps({ anotaciones: { type: Object, required: true } })
</script>

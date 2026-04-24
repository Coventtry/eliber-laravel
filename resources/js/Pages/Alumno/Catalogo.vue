<template>
    <Head title="Catálogo" />
    <AppNavbarAlumno />

    <div class="container page-content">
        <FlashMessage />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Catálogo de materiales disponibles</h4>
            <span class="text-muted small">{{ materiales.total }} material{{ materiales.total !== 1 ? 'es' : '' }}</span>
        </div>

        <div v-if="materiales.data.length === 0" class="alert alert-info">
            No hay materiales disponibles en este momento.
        </div>

        <div v-else class="row">
            <div v-for="m in materiales.data" :key="m.id" class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-1">{{ m.titulo }}</h6>
                        <p class="text-muted small mb-1">{{ m.autor }}</p>
                        <p class="text-muted small mb-2">
                            <span v-if="m.area" class="badge badge-light border mr-1">{{ m.area }}</span>
                            <span v-if="m.anio">{{ m.anio }}</span>
                        </p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center py-2">
                        <small class="text-muted">{{ m.categoria }}</small>
                        <span class="badge badge-success">{{ m.disponibilidad }} disponible{{ m.disponibilidad !== 1 ? 's' : '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <nav v-if="materiales.last_page > 1" class="mt-2">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item" :class="{ disabled: !materiales.prev_page_url }">
                    <Link class="page-link" :href="materiales.prev_page_url ?? '#'">Anterior</Link>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">{{ materiales.current_page }} / {{ materiales.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: !materiales.next_page_url }">
                    <Link class="page-link" :href="materiales.next_page_url ?? '#'">Siguiente</Link>
                </li>
            </ul>
        </nav>
    </div>

    <AppFooter />
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppNavbarAlumno from '@/Components/AppNavbarAlumno.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    materiales: { type: Object, required: true },
})
</script>

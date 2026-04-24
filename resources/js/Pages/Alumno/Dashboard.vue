<template>
    <Head title="Inicio" />
    <AppNavbarAlumno />

    <div class="container page-content">
        <FlashMessage />

        <div class="mb-4">
            <h4 class="mb-0">Bienvenido, {{ auth.user.nombre }}</h4>
            <small class="text-muted">Biblioteca E-liber</small>
        </div>

        <div class="row">
            <!-- Accesos rápidos -->
            <div class="col-md-4 mb-4">
                <Link :href="route('alumno.catalogo')" class="card shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-book" style="font-size:2.5rem; color:var(--eliber-primary);"></i>
                        <h6 class="mt-2 mb-0">Catálogo de materiales</h6>
                        <small class="text-muted">Consultá el material disponible</small>
                    </div>
                </Link>
            </div>
            <div class="col-md-4 mb-4">
                <Link :href="route('alumno.reservas')" class="card shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-bookmark-check" style="font-size:2.5rem; color:var(--eliber-primary);"></i>
                        <h6 class="mt-2 mb-0">Mis reservas</h6>
                        <small class="text-muted">Revisá el estado de tus solicitudes</small>
                    </div>
                </Link>
            </div>
            <div class="col-md-4 mb-4">
                <Link :href="route('perfil.edit')" class="card shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-person-gear" style="font-size:2.5rem; color:var(--eliber-primary);"></i>
                        <h6 class="mt-2 mb-0">Mi perfil</h6>
                        <small class="text-muted">Actualizá tu correo y foto</small>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Últimas reservas -->
        <div v-if="tiene_socio">
            <h5 class="mb-3">Reservas recientes</h5>

            <div v-if="reservas_recientes.length === 0" class="alert alert-info">
                Todavía no tenés reservas registradas.
            </div>

            <div v-else class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Material</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in reservas_recientes" :key="r.id">
                                <td>{{ r.material }}</td>
                                <td><span class="badge" :class="badgeEstado(r.estado)">{{ r.estado }}</span></td>
                                <td>{{ r.fecha }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-right mt-2">
                <Link :href="route('alumno.reservas')" class="btn btn-sm btn-outline-primary">Ver todas</Link>
            </div>
        </div>

        <div v-else class="alert alert-warning">
            <i class="bi bi-info-circle mr-2"></i>
            Tu usuario no está vinculado a un registro de socio. Contactá al bibliotecario para que lo configure.
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppNavbarAlumno from '@/Components/AppNavbarAlumno.vue'
import AppFooter from '@/Components/AppFooter.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

defineProps({
    reservas_recientes: { type: Array, default: () => [] },
    tiene_socio:        { type: Boolean, default: false },
})

const page = usePage()
const auth = computed(() => page.props.auth)

function badgeEstado(estado) {
    return {
        'badge-warning': estado === 'pendiente',
        'badge-success': estado === 'aprobada',
        'badge-danger':  estado === 'rechazada' || estado === 'cancelada',
        'badge-secondary': estado === 'expirada',
    }
}
</script>

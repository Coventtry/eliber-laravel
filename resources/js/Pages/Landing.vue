<template>
    <Head title="Biblioteca">
        <meta name="description" content="E-liber - Sistema de Gestión de Biblioteca Escolar">
    </Head>

    <PublicNavbar />

    <header class="hero-section text-center">
        <div class="container">
           
            <h1 class="display-4 font-weight-bold animate-fade-in-up">Biblioteca Escolar</h1>
            <p class="lead animate-slide-in-left">Sistema de Gestión Bibliotecaria</p>
        </div>
    </header>

    <div class="container page-content py-5">
        <h2 class="mb-4 text-center text-eliber">Novedades</h2>
        <div v-if="noticias && noticias.length" class="row">
            <div v-for="(noticia, index) in noticias" :key="noticia.id" class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card card-noticia h-100" :style="{ animationDelay: (index * 0.1) + 's' }">
                    <div v-if="noticia.imagen_url" class="card-img-top">
                        <img :src="noticia.imagen_url" :alt="noticia.titulo" class="img-fluid">
                    </div>
                    <div v-else class="card-img-top d-flex align-items-center justify-content-center bg-crema">
                        <i class="bi bi-newspaper" style="font-size:3rem;color:var(--eliber-primary);opacity:.3;"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ noticia.titulo }}</h5>
                        <p class="card-text text-muted small">{{ noticia.descripcion }}</p>
                    </div>
                    <div class="card-footer text-muted bg-crema">
                        <small><i class="bi bi-calendar3 me-1"></i>{{ formatearFecha(noticia.fecha) }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-muted py-5">
            <i class="bi bi-journal-text" style="font-size:3rem;opacity:.3;"></i>
            <p class="mt-3">No hay novedades disponibles.</p>
        </div>
    </div>

    <footer class="footer-eliber text-center py-3">
        <div class="container">
            <small>&copy; {{ anio }} E-liber — Sistema de Gestión de Biblioteca</small>
        </div>
    </footer>
</template>

<script setup>
import PublicNavbar from '@/Components/PublicNavbar.vue'

defineProps({
    noticias: {
        type: Array,
        default: () => []
    }
});

const anio = new Date().getFullYear();

function formatearFecha(date) {
    if (!date) return '';
    const d = new Date(date);
    return isNaN(d) ? '' : d.toLocaleDateString('es-AR');
}
</script>


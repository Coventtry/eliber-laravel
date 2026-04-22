<template>
    <Head title="Biblioteca" />

    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand font-weight-bold">
                <i class="bi bi-book-half mr-2"></i>E-liber
            </span>
            <div class="ml-auto">
                <Link :href="route('login')" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-in-right mr-1"></i>Ingresar
                </Link>
            </div>
        </div>
    </nav>

    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 font-weight-bold">Biblioteca Escolar</h1>
            <p class="lead">Sistema de Gestión Bibliotecaria</p>
        </div>
    </header>

    <div class="container page-content py-5">
        <h2 class="mb-4 text-center">Novedades</h2>
        <div v-if="noticias && noticias.length" class="row">
            <div v-for="noticia in noticias" :key="noticia.id" class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div v-if="noticia.imagen" class="card-img-top news-img">
                        <img :src="'/storage/noticias/' + noticia.imagen" :alt="noticia.titulo" class="img-fluid">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ noticia.titulo }}</h5>
                        <p class="card-text text-muted small">{{ noticia.descripcion }}</p>
                    </div>
                    <div class="card-footer text-muted">
                        <small>{{ formatDate(noticia.created_at) }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-muted py-5">
            <p>No hay novedades disponibles.</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    noticias: {
        type: Array,
        default: () => []
    }
});

function formatDate(date) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-AR');
}
</script>

<style scoped>
.hero-section {
    background: #2e7d32;
    color: white;
    padding: 4rem 1rem;
}

.news-img {
    height: 200px;
    overflow: hidden;
    background: #f5f5f5;
}

.news-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
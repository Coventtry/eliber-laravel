<template>
    <footer class="footer-eliber text-center mt-auto">
        <div class="container">
            E-liber &copy; {{ year }} — Sistema de Gestión de Biblioteca
        </div>
    </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>

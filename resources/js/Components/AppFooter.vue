<template>
    <footer class="footer-eliber mt-auto">
        <div class="container">
            <div class="row footer-grid">
                <div class="col-md-4 footer-section">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><Link :href="route('dashboard')">Inicio</Link></li>
                        <li><Link :href="route('acerca')">Acerca del proyecto</Link></li>
                        <li><Link :href="route('faqs')">FAQs</Link></li>
                    </ul>
                </div>
                <div class="col-md-4 footer-section">
                    <h4>Repositorio</h4>
                    <ul>
                        <li><a href="https://github.com/Coventtry/eliber-laravel" target="_blank">GitHub del proyecto</a></li>
                    </ul>
                </div>
                <div class="col-md-4 footer-section text-center text-md-right">
                    <div class="tech-icons-wrapper">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/html5/html5-original.svg" alt="HTML5" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/css3/css3-original.svg" alt="CSS3" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/bootstrap/bootstrap-original.svg" alt="Bootstrap" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" alt="PHP" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/vuejs/vuejs-original.svg" alt="Vue.js" width="32" height="32">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/laravel/laravel-original.svg" alt="Laravel" width="32" height="32">
                    </div>
                    <div class="social-icons">
                        <a href="https://github.com/Coventtry" target="_blank" aria-label="GitHub">
                            <i class="bi bi-github"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center">
                &copy; {{ year }} E-liber — Sistema de Gestión de Biblioteca. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>
<template>
    <Head title="FAQs - Preguntas Frecuentes" />
    <AppNavbar />

    <div class="hero-section text-center">
        <div class="container">
            <img src="/img/logo.png" alt="E-liber" height="100" class="mb-3 animate-pulse-subtle">
            <h1 class="display-4 font-weight-bold">Preguntas Frecuentes</h1>
            <p class="lead">FAQs</p>
        </div>
    </div>

    <div class="container page-content py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body">
                        <h4 class="mb-3" style="color: var(--eliber-primary);">Requisitos Previos</h4>
                        <p>Asegúrate de tener lo siguiente instalado en tu computadora:</p>
                        <ul>
                            <li>PHP 8.1+</li>
                            <li>MySQL/MariaDB</li>
                            <li>Composer</li>
                            <li>Node.js (para compilar assets)</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body">
                        <h4 class="mb-3" style="color: var(--eliber-primary);">Pasos para Instalar</h4>
                        <ol>
                            <li class="mb-2"><strong>Descargar el Repositorio</strong><br>Clona o descarga el proyecto desde GitHub</li>
                            <li class="mb-2"><strong>Instalar Dependencias</strong><br>Ejecuta <code>composer install</code> y <code>npm install</code></li>
                            <li class="mb-2"><strong>Configurar Variables de Entorno</strong><br>Copia <code>.env.example</code> a <code>.env</code> y configura la base de datos</li>
                            <li class="mb-2"><strong>Generar Clave de App</strong><br>Ejecuta <code>php artisan key:generate</code></li>
                            <li class="mb-2"><strong>Ejecutar Migraciones</strong><br>Ejecuta <code>php artisan migrate</code></li>
                            <li class="mb-2"><strong>Ejecutar Seeders</strong><br>Ejecuta <code>php artisan db:seed</code> para datos de prueba</li>
                            <li class="mb-2"><strong>Iniciar Servidor</strong><br>Ejecuta <code>composer run dev</code></li>
                        </ol>
                    </div>
                </div>

                <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body">
                        <h4 class="mb-3" style="color: var(--eliber-primary);">Credenciales por Defecto</h4>
                        <p>Después de ejecutar los seeders:</p>
                        <ul>
                            <li><strong>Usuario:</strong> admin</li>
                            <li><strong>Contraseña:</strong> password</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body">
                        <h4 class="mb-3" style="color: var(--eliber-primary);">¿Cómo obtener ayuda?</h4>
                        <p>Si tienes más preguntas, puedes:</p>
                        <ul>
                            <li>Abrir un issue en GitHub</li>
                            <li>Consultar la documentación en <code>docs/OPERACION.md</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <AppFooter />
</template>

<script setup>
import AppNavbar from '@/Components/AppNavbar.vue'
import AppFooter from '@/Components/AppFooter.vue'
</script>
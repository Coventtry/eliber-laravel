<template>
    <div class="admin-shell">
        <!-- Navbar top -->
        <nav class="admin-navbar navbar-dark d-flex align-items-center px-3">
            <Link :href="route('admin.dashboard')" class="admin-brand d-flex align-items-center mr-auto">
                <img src="/img/logo.png" alt="E-liber" height="32">
                <span class="ml-2 font-weight-bold text-white" style="font-size:.95rem;">Panel Admin</span>
            </Link>

            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center p-0" data-toggle="dropdown" href="#">
                        <template v-if="auth.user.picture_url">
                            <img :src="auth.user.picture_url" class="perfil-img mr-2" alt="perfil">
                        </template>
                        <template v-else>
                            <i class="bi bi-person-circle text-white" style="font-size:1.4rem;"></i>
                        </template>
                        <span class="text-white ml-1 d-none d-md-inline" style="font-size:.9rem;">{{ auth.user.nombre }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right mt-2" style="min-width:180px;">
                        <Link :href="route('perfil.edit')" class="dropdown-item">
                            <i class="bi bi-person-gear mr-2"></i>Mi perfil
                        </Link>
                        <div class="dropdown-divider"></div>
                        <form @submit.prevent="logout">
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right mr-2"></i>Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="d-flex flex-column h-100 pt-2">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Separador de sección -->
                    <div v-if="item.separator" class="admin-menu-sep">{{ item.label }}</div>

                    <!-- Link normal -->
                    <component
                        v-else
                        :is="item.disabled ? 'span' : Link"
                        :href="item.disabled ? undefined : item.href"
                        class="admin-menu-item"
                        :class="{
                            'active': isActive(item),
                            'disabled': item.disabled
                        }"
                        :title="item.disabled ? 'Próximamente' : ''"
                    >
                        <i :class="['bi', item.icon, 'admin-menu-icon']"></i>
                        <span>{{ item.label }}</span>
                        <span v-if="item.badge" class="badge badge-danger ml-auto">{{ item.badge }}</span>
                        <span v-if="item.disabled" class="badge badge-secondary ml-auto" style="font-size:.6rem;">pronto</span>
                    </component>
                </template>
            </nav>
        </aside>

        <!-- Contenido principal -->
        <main class="admin-content">
            <FlashMessage />
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import FlashMessage from '@/Components/FlashMessage.vue'

const page = usePage()
const auth = computed(() => page.props.auth)
const alertas = computed(() => page.props.alertas_no_leidas ?? 0)

function logout() { router.post(route('logout')) }

function isActive(item) {
    if (!item.href) return false
    return page.url.startsWith(new URL(item.href, window.location.origin).pathname)
}

const menuItems = computed(() => [
    { name: 'overview',   label: 'Overview',    icon: 'bi-grid-1x2',        href: route('admin.dashboard') },
    { separator: true,    label: 'GESTIÓN' },
    { name: 'usuarios',   label: 'Usuarios',    icon: 'bi-people',           href: route('admin.usuarios') },
    { name: 'socios',     label: 'Socios',      icon: 'bi-person-badge',     href: route('socios.index') },
    { name: 'materiales', label: 'Materiales',  icon: 'bi-book',             href: route('materiales.index') },
    { name: 'prestamos',  label: 'Préstamos',   icon: 'bi-journal-arrow-up', href: route('prestamos.index') },
    { name: 'alertas',    label: 'Alertas',     icon: 'bi-bell',             href: route('alertas.index'), badge: alertas.value || null },
    { separator: true,    label: 'CONTENIDO' },
    { name: 'feedback',   label: 'Feedback',    icon: 'bi-kanban',           disabled: true },
    { name: 'contenido',  label: 'Contenido',   icon: 'bi-file-earmark-text', disabled: true },
    { separator: true,    label: 'SISTEMA' },
    { name: 'analitica',  label: 'Analítica',   icon: 'bi-bar-chart-line',   disabled: true },
    { name: 'config',     label: 'Configuración', icon: 'bi-gear',           disabled: true },
])
</script>

<style scoped>
/* Shell */
.admin-shell {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Navbar */
.admin-navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 56px;
    z-index: 1040;
    background: var(--eliber-primary, #1a3a5c);
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}
.admin-brand { text-decoration: none; }

/* Sidebar */
.admin-sidebar {
    position: fixed;
    top: 56px; left: 0; bottom: 0;
    width: 220px;
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    overflow-y: auto;
    z-index: 1030;
}

.admin-menu-sep {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .08em;
    color: #adb5bd;
    padding: .75rem 1rem .25rem;
    text-transform: uppercase;
}

.admin-menu-item {
    display: flex;
    align-items: center;
    padding: .5rem 1rem;
    color: #495057;
    text-decoration: none;
    font-size: .88rem;
    border-left: 3px solid transparent;
    transition: background .15s, border-color .15s;
    cursor: pointer;
}
.admin-menu-item:hover:not(.disabled) {
    background: #e9ecef;
    color: var(--eliber-primary, #1a3a5c);
    text-decoration: none;
}
.admin-menu-item.active {
    background: #e8f0fe;
    border-left-color: var(--eliber-primary, #1a3a5c);
    color: var(--eliber-primary, #1a3a5c);
    font-weight: 600;
}
.admin-menu-item.disabled {
    opacity: .45;
    cursor: default;
}
.admin-menu-icon {
    font-size: 1rem;
    margin-right: .6rem;
    width: 1.2rem;
    text-align: center;
}

/* Content */
.admin-content {
    margin-left: 220px;
    margin-top: 56px;
    padding: 1.5rem;
    min-height: calc(100vh - 56px);
    background: #f4f6f9;
}

/* Perfil img pequeña */
.perfil-img {
    width: 30px; height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

@media (max-width: 768px) {
    .admin-sidebar { display: none; }
    .admin-content { margin-left: 0; }
}
</style>

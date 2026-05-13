<template>
    <div class="admin-shell">

        <!-- Navbar top -->
        <nav class="admin-navbar d-flex align-items-center px-3">

            <!-- Hamburger (mobile) -->
            <button class="sidebar-hamburger d-md-none mr-2" @click="sidebarOpen = !sidebarOpen" aria-label="Menú">
                <i :class="sidebarOpen ? 'bi bi-x-lg' : 'bi bi-list'"></i>
            </button>

            <!-- Brand -->
            <Link :href="route('admin.dashboard')" class="admin-brand d-flex align-items-center mr-auto">
                <img src="/img/logo.png" alt="E-liber" height="30">
                <span class="admin-brand-text ml-2">Panel Admin</span>
            </Link>

            <!-- Right controls -->
            <div class="d-flex align-items-center gap-3">

                <!-- Dark mode toggle -->
                <button class="dm-toggle" @click="toggleDark" :title="darkMode ? 'Modo claro' : 'Modo oscuro'" :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'">
                    <i :class="darkMode ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
                </button>

                <!-- User dropdown -->
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center p-0" data-toggle="dropdown" href="#" style="gap:.6rem;">
                        <img v-if="auth.user.picture_url"
                            :src="auth.user.picture_url"
                            class="nav-avatar"
                            alt="perfil">
                        <div v-else class="nav-avatar-initials">
                            {{ auth.user.nombre?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div class="d-none d-md-flex flex-column nav-user-line">
                             <span class="nav-user-name">{{ auth.user.nombre }}</span>
                             <span class="nav-user-role">Administrador</span>
                         </div>
                         <i class="bi bi-chevron-down nav-user-role icon-sm"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right admin-dropdown mt-2">
                        <div class="px-3 py-2 border-bottom">
                            <div class="font-weight-bold" style="font-size:.82rem;">{{ auth.user.nombre }}</div>
                            <div class="text-muted small">@{{ auth.user.usuario }}</div>
                        </div>
                        <Link :href="route('perfil.edit')" class="dropdown-item admin-dropdown-item">
                            <i class="bi bi-person-gear"></i>Mi perfil
                        </Link>
                        <div class="dropdown-divider my-1"></div>
                        <form @submit.prevent="logout">
                            <button type="submit" class="dropdown-item admin-dropdown-item text-danger" :disabled="cargando">
                                <span v-if="cargando" class="spinner-border spinner-border-sm mr-1"></span>
                                <i v-else class="bi bi-box-arrow-right"></i>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Overlay backdrop (mobile) -->
        <div v-if="sidebarOpen" class="sidebar-overlay d-md-none" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside class="admin-sidebar" :class="{ 'sidebar-open': sidebarOpen }">
            <nav class="d-flex flex-column h-100 pt-2 pb-3">
                <template v-for="item in menuItems" :key="item.name ?? item.label">
                    <div v-if="item.separator" class="admin-menu-sep">{{ item.label }}</div>
                    <component
                        v-else
                        :is="item.disabled ? 'span' : Link"
                        :href="item.disabled ? undefined : item.href"
                        class="admin-menu-item"
                        :class="{ 'active': isActive(item), 'disabled': item.disabled }"
                        :title="item.disabled ? 'Próximamente' : ''"
                    >
                        <i :class="['bi', item.icon, 'admin-menu-icon']"></i>
                        <span>{{ item.label }}</span>
                        <span v-if="item.badge" class="badge badge-danger ml-auto">{{ item.badge }}</span>
                        <span v-if="item.disabled" class="admin-badge-soon ml-auto">pronto</span>
                    </component>
                </template>
            </nav>
        </aside>

        <!-- Main content -->
        <main id="main-content" class="admin-content">
            <FlashMessage />
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import FlashMessage from '@/Components/FlashMessage.vue'
import { initDarkMode, useDarkMode } from '@/Composables/useDarkMode'

const page        = usePage()
const auth        = computed(() => page.props.auth)
const sidebarOpen = ref(false)
const cargando    = ref(false)

const { darkMode, toggleDark } = useDarkMode()

onMounted(() => {
    initDarkMode(auth.value?.user?.id ?? null)
})

// ── Auth / nav ────────────────────────────────────────────────────────────────
function logout() {
    cargando.value = true
    router.post(route('logout'), {}, {
        onStart: () => { cargando.value = true },
    })
}

function isActive(item) {
    if (!item.href) return false
    return page.url.startsWith(new URL(item.href, window.location.origin).pathname)
}

const menuItems = computed(() => [
    { name: 'overview',  label: 'Overview',      icon: 'bi-grid-1x2',         href: route('admin.dashboard') },
    { separator: true,   label: 'GESTIÓN' },
    { name: 'usuarios',  label: 'Usuarios',      icon: 'bi-people',            href: route('admin.usuarios') },
    { separator: true,   label: 'CONTENIDO' },
    { name: 'feedback',  label: 'Feedback',      icon: 'bi-kanban',            href: route('admin.feedback.index') },
    { name: 'contenido', label: 'Contenido',     icon: 'bi-file-earmark-text', href: route('admin.contenido.index') },
    { separator: true,   label: 'SISTEMA' },
    { name: 'analitica', label: 'Analítica',     icon: 'bi-bar-chart-line',    href: route('admin.analitica.index') },
    { name: 'config',    label: 'Configuración', icon: 'bi-gear',              href: route('admin.configuracion.index') },
])
</script>

<style scoped>
/* ── Variables: Light ────────────────────────────────────────────────────── */
.admin-shell {
    --nav-bg:          #1a3a5c;
    --nav-border:      rgba(255,255,255,.08);
    --sidebar-bg:      #ffffff;
    --sidebar-border:  #e5e7eb;
    --sidebar-sep:     #9ca3af;
    --item-color:      #374151;
    --item-hover-bg:   #f3f4f6;
    --item-hover-color:#1a3a5c;
    --item-active-bg:  #eff6ff;
    --item-active-border:#1a3a5c;
    --item-active-color:#1a3a5c;
    --content-bg:      #f1f5f9;
    --card-bg:         #ffffff;
    --card-border:     #e5e7eb;
    --text-primary:    #111827;
    --text-muted:      #6b7280;
    --dropdown-bg:     #ffffff;
    --dropdown-border: #e5e7eb;
    --dropdown-hover:  #f9fafb;
    --toggle-bg:       rgba(255,255,255,.15);
    --toggle-color:    #fbbf24;

    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: background .2s;
}

/* ── Variables: Dark ─────────────────────────────────────────────────────── */
[data-theme="dark"] .admin-shell {
    --nav-bg:          var(--dm-bg);
    --nav-border:      rgba(255,255,255,.06);
    --sidebar-bg:      var(--dm-surface);
    --sidebar-border:  var(--dm-border);
    --sidebar-sep:     #475569;
    --item-color:      #cbd5e1;
    --item-hover-bg:   var(--dm-hover);
    --item-hover-color:#93c5fd;
    --item-active-bg:  var(--dm-primary-muted);
    --item-active-border:var(--dm-primary);
    --item-active-color:#93c5fd;
    --content-bg:      var(--dm-bg);
    --card-bg:         var(--dm-surface);
    --card-border:     var(--dm-border);
    --text-primary:    var(--dm-text);
    --text-muted:      var(--dm-text-muted);
    --dropdown-bg:     var(--dm-surface);
    --dropdown-border: var(--dm-border);
    --dropdown-hover:  var(--dm-hover);
    --toggle-bg:       rgba(255,255,255,.1);
    --toggle-color:    #fbbf24;
}

/* ── Navbar ──────────────────────────────────────────────────────────────── */
.admin-navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 56px;
    z-index: 1040;
    background: var(--nav-bg);
    box-shadow: 0 1px 0 var(--nav-border), 0 2px 12px rgba(0,0,0,.2);
}

.admin-brand { text-decoration: none; }
.admin-brand-text {
    font-size: .9rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: -.01em;
}

/* Avatar */
.nav-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.25);
    flex-shrink: 0;
}
.nav-avatar-initials {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #e8a020;
    color: #fff;
    font-weight: 700;
    font-size: .88rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.25);
}
.nav-user-name  { color: #fff; font-weight: 600; font-size: .82rem; }
.nav-user-role  { color: rgba(255,255,255,.55); font-size: .68rem; }

/* Dropdown */
.admin-dropdown {
    background: var(--dropdown-bg) !important;
    border: 1px solid var(--dropdown-border) !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 24px rgba(0,0,0,.15) !important;
    min-width: 190px;
    padding: 0;
    overflow: hidden;
}
.admin-dropdown-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem 1rem;
    font-size: .83rem;
    color: var(--item-color) !important;
    transition: background .12s;
}
.admin-dropdown-item:hover { background: var(--dropdown-hover) !important; }
.admin-dropdown-item.text-danger { color: #ef4444 !important; }

/* ── Sidebar ─────────────────────────────────────────────────────────────── */
.admin-sidebar {
    position: fixed;
    top: 56px; left: 0; bottom: 0;
    width: 220px;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--sidebar-border);
    overflow-y: auto;
    z-index: 1030;
    transition: background .2s, border-color .2s;
}

.admin-menu-sep {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .09em;
    color: var(--sidebar-sep);
    padding: .9rem 1rem .3rem;
    text-transform: uppercase;
}

.admin-menu-item {
    display: flex;
    align-items: center;
    padding: .52rem 1rem;
    color: var(--item-color);
    text-decoration: none;
    font-size: .85rem;
    border-left: 3px solid transparent;
    transition: background .12s, border-color .12s, color .12s;
    cursor: pointer;
    user-select: none;
}
.admin-menu-item:hover:not(.disabled) {
    background: var(--item-hover-bg);
    color: var(--item-hover-color);
    text-decoration: none;
}
.admin-menu-item.active {
    background: var(--item-active-bg);
    border-left-color: var(--item-active-border);
    color: var(--item-active-color);
    font-weight: 600;
}
.admin-menu-item.disabled { opacity: .4; cursor: default; }

.admin-menu-icon {
    font-size: .95rem;
    margin-right: .6rem;
    width: 1.15rem;
    text-align: center;
    flex-shrink: 0;
}

.admin-badge-soon {
    margin-left: auto;
    font-size: .58rem;
    font-weight: 600;
    padding: .1rem .38rem;
    border-radius: 4px;
    background: var(--item-hover-bg);
    color: var(--text-muted);
    letter-spacing: .03em;
    text-transform: uppercase;
}

/* ── Main content ────────────────────────────────────────────────────────── */
.admin-content {
    margin-left: 220px;
    margin-top: 56px;
    padding: 1.75rem;
    min-height: calc(100vh - 56px);
    background: var(--content-bg);
    color: var(--text-primary);
    transition: background .2s, color .2s;
}

/* ── Hamburger ───────────────────────────────────────────────────────────── */
.sidebar-hamburger {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.15);
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: background .15s;
}
.sidebar-hamburger:hover { background: rgba(255,255,255,.22); }

/* ── Overlay ─────────────────────────────────────────────────────────────── */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1029;
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 767px) {
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform .25s ease;
        z-index: 1030;
    }
    .admin-sidebar.sidebar-open {
        transform: translateX(0);
    }
    .admin-content { margin-left: 0; }
}
@media (min-width: 768px) {
    .admin-sidebar { transform: none !important; }
}
</style>

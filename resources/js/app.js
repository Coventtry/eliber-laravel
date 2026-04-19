import { createApp, h } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue, route } from '../../vendor/tightenco/ziggy'

window.route = route

// Bootstrap 4 — requiere jQuery global antes del import
import $ from 'jquery'
window.jQuery = window.$ = $
import 'popper.js'
import 'bootstrap'

createInertiaApp({
    title: (title) => title ? `${title} — E-liber` : 'E-liber',
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Link', Link)
            .component('Head', Head)
            .mount(el)
    },
    progress: {
        color: '#28a745',
    },
})

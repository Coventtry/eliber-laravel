import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { createRequire } from 'module'

const require = createRequire(import.meta.url)

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // Bootstrap 4 requires jQuery as a global — expose it for all modules
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            // Bootstrap 4 needs jQuery as a window global
            jquery: require.resolve('jquery/dist/jquery.js'),
        },
    },
    optimizeDeps: {
        include: ['jquery', 'popper.js', 'bootstrap'],
    },
})

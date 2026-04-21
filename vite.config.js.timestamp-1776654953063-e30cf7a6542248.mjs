// vite.config.js
import { defineConfig } from "file:///C:/Users/rodri/Documents/eliber-laravel/node_modules/vite/dist/node/index.js";
import laravel from "file:///C:/Users/rodri/Documents/eliber-laravel/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///C:/Users/rodri/Documents/eliber-laravel/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import { createRequire } from "module";
var __vite_injected_original_import_meta_url = "file:///C:/Users/rodri/Documents/eliber-laravel/vite.config.js";
var require2 = createRequire(__vite_injected_original_import_meta_url);
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    })
  ],
  // Bootstrap 4 requires jQuery as a global — expose it for all modules
  define: {
    __VUE_OPTIONS_API__: true,
    __VUE_PROD_DEVTOOLS__: false
  },
  resolve: {
    alias: {
      "@": "/resources/js",
      // Bootstrap 4 needs jQuery as a window global
      jquery: require2.resolve("jquery/dist/jquery.js")
    }
  },
  optimizeDeps: {
    include: ["jquery", "popper.js", "bootstrap"]
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJDOlxcXFxVc2Vyc1xcXFxyb2RyaVxcXFxEb2N1bWVudHNcXFxcZWxpYmVyLWxhcmF2ZWxcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIkM6XFxcXFVzZXJzXFxcXHJvZHJpXFxcXERvY3VtZW50c1xcXFxlbGliZXItbGFyYXZlbFxcXFx2aXRlLmNvbmZpZy5qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vQzovVXNlcnMvcm9kcmkvRG9jdW1lbnRzL2VsaWJlci1sYXJhdmVsL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSdcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nXG5pbXBvcnQgdnVlIGZyb20gJ0B2aXRlanMvcGx1Z2luLXZ1ZSdcbmltcG9ydCB7IGNyZWF0ZVJlcXVpcmUgfSBmcm9tICdtb2R1bGUnXG5cbmNvbnN0IHJlcXVpcmUgPSBjcmVhdGVSZXF1aXJlKGltcG9ydC5tZXRhLnVybClcblxuZXhwb3J0IGRlZmF1bHQgZGVmaW5lQ29uZmlnKHtcbiAgICBwbHVnaW5zOiBbXG4gICAgICAgIGxhcmF2ZWwoe1xuICAgICAgICAgICAgaW5wdXQ6IFsncmVzb3VyY2VzL2Nzcy9hcHAuY3NzJywgJ3Jlc291cmNlcy9qcy9hcHAuanMnXSxcbiAgICAgICAgICAgIHJlZnJlc2g6IHRydWUsXG4gICAgICAgIH0pLFxuICAgICAgICB2dWUoe1xuICAgICAgICAgICAgdGVtcGxhdGU6IHtcbiAgICAgICAgICAgICAgICB0cmFuc2Zvcm1Bc3NldFVybHM6IHtcbiAgICAgICAgICAgICAgICAgICAgYmFzZTogbnVsbCxcbiAgICAgICAgICAgICAgICAgICAgaW5jbHVkZUFic29sdXRlOiBmYWxzZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfSxcbiAgICAgICAgfSksXG4gICAgXSxcbiAgICAvLyBCb290c3RyYXAgNCByZXF1aXJlcyBqUXVlcnkgYXMgYSBnbG9iYWwgXHUyMDE0IGV4cG9zZSBpdCBmb3IgYWxsIG1vZHVsZXNcbiAgICBkZWZpbmU6IHtcbiAgICAgICAgX19WVUVfT1BUSU9OU19BUElfXzogdHJ1ZSxcbiAgICAgICAgX19WVUVfUFJPRF9ERVZUT09MU19fOiBmYWxzZSxcbiAgICB9LFxuICAgIHJlc29sdmU6IHtcbiAgICAgICAgYWxpYXM6IHtcbiAgICAgICAgICAgICdAJzogJy9yZXNvdXJjZXMvanMnLFxuICAgICAgICAgICAgLy8gQm9vdHN0cmFwIDQgbmVlZHMgalF1ZXJ5IGFzIGEgd2luZG93IGdsb2JhbFxuICAgICAgICAgICAganF1ZXJ5OiByZXF1aXJlLnJlc29sdmUoJ2pxdWVyeS9kaXN0L2pxdWVyeS5qcycpLFxuICAgICAgICB9LFxuICAgIH0sXG4gICAgb3B0aW1pemVEZXBzOiB7XG4gICAgICAgIGluY2x1ZGU6IFsnanF1ZXJ5JywgJ3BvcHBlci5qcycsICdib290c3RyYXAnXSxcbiAgICB9LFxufSlcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBaVQsU0FBUyxvQkFBb0I7QUFDOVUsT0FBTyxhQUFhO0FBQ3BCLE9BQU8sU0FBUztBQUNoQixTQUFTLHFCQUFxQjtBQUhpSyxJQUFNLDJDQUEyQztBQUtoUCxJQUFNQSxXQUFVLGNBQWMsd0NBQWU7QUFFN0MsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDeEIsU0FBUztBQUFBLElBQ0wsUUFBUTtBQUFBLE1BQ0osT0FBTyxDQUFDLHlCQUF5QixxQkFBcUI7QUFBQSxNQUN0RCxTQUFTO0FBQUEsSUFDYixDQUFDO0FBQUEsSUFDRCxJQUFJO0FBQUEsTUFDQSxVQUFVO0FBQUEsUUFDTixvQkFBb0I7QUFBQSxVQUNoQixNQUFNO0FBQUEsVUFDTixpQkFBaUI7QUFBQSxRQUNyQjtBQUFBLE1BQ0o7QUFBQSxJQUNKLENBQUM7QUFBQSxFQUNMO0FBQUE7QUFBQSxFQUVBLFFBQVE7QUFBQSxJQUNKLHFCQUFxQjtBQUFBLElBQ3JCLHVCQUF1QjtBQUFBLEVBQzNCO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxLQUFLO0FBQUE7QUFBQSxNQUVMLFFBQVFBLFNBQVEsUUFBUSx1QkFBdUI7QUFBQSxJQUNuRDtBQUFBLEVBQ0o7QUFBQSxFQUNBLGNBQWM7QUFBQSxJQUNWLFNBQVMsQ0FBQyxVQUFVLGFBQWEsV0FBVztBQUFBLEVBQ2hEO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFsicmVxdWlyZSJdCn0K

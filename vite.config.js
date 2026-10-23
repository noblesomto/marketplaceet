import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        visualizer({ open: true }),
    ],
    build: {
        outDir: 'public/build',
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
            mangle: {
                toplevel: true,
            },
            format: {
                comments: false,
            },
        },
        cssMinify: true,

        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        // Heavy libraries get their own chunks
                        if (id.includes('axios')) return 'vendor-axios';
                        if (id.includes('trix')) return 'vendor-trix';
                        if (id.includes('sweetalert2')) return 'vendor-sweetalert';
                        if (id.includes('pusher-js')) return 'vendor-pusher';
                        if (id.includes('laravel-echo')) return 'vendor-echo';

                        // Everything else
                        return 'vendor';
                    }
                },
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
        },

        chunkSizeWarningLimit: 600,
    },
});

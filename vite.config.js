import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react'
import inertia from '@inertiajs/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        tailwindcss(),
        inertia({
            ssr: false,
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost'
        },
        watch: {
            ignored: ['**/storage/**', '**/node_modules/**'],
        },
    },
});

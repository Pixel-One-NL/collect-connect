import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react'
import inertia from '@inertiajs/vite'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const port = Number(env.VITE_PORT || 5173);

    return {
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
            // Docker publishes exactly this port, so fail instead of silently picking another.
            port,
            strictPort: true,
            hmr: {
                host: 'localhost',
                port,
            },
            watch: {
                ignored: ['**/storage/**', '**/node_modules/**'],
            },
        },
    };
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/pages/penyelia.js',
                'resources/js/pages/manager.js',
                'resources/js/pages/layananjasa.js',
                'resources/js/pages/keuangan.js',
                'resources/js/pages/permission.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        // host: '192.168.18.16',
    }
});

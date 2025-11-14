import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        // host: '192.168.18.16',
    },
    css: {
        preprocessorOptions: {
            scss: {
                // Opsi ini akan meredam peringatan yang berasal dari dependensi
                // (seperti Bootstrap) yang masih menggunakan @import atau fungsi lama.
                silenceDeprecations: ['import', 'legacy-js-api', 'global-builtin'],
                quietDeps: true, // Beberapa versi/konfigurasi Vite juga mendukung ini
            }
        }
    }
});

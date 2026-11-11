import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/site.css',
                'resources/js/admin/app.ts',
                'resources/js/account/app.ts'
            ],
            ssr: [
                'resources/js/admin/ssr.ts',
                'resources/js/account/ssr.ts'
            ],
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
    server: {
        cors: true, // Разрешает CORS для всех источников в dev-режиме
        origin: 'http://127.0.0.1:5175', // Явно указываем адрес сервера Vite
        // Если порт меняется, можно указать фиксированный порт:
        port: 5175,
        strictPort: true,
    },
    resolve: {
        alias: {
            '@admin': path.resolve(__dirname, './resources/js/admin'),
            '@account': path.resolve(__dirname, './resources/js/account'),
            'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});

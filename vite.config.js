import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

const appBase = process.env.VITE_APP_BASE || '/';

export default defineConfig({
    base: appBase,
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: false,
            injectRegister: null,
            filename: 'sw.js',
            workbox: {
                navigateFallback: null,
                globPatterns: ['**/*.{js,css,woff2,png,svg,ico,webmanifest}'],
                globIgnores: ['**/hot', '**/storage/**'],
            },
            devOptions: {
                enabled: false,
            },
        }),
    ],
});

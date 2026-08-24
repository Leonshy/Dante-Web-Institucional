import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // docs/04-ui-design-system.md §2 — tipografía de marca aprobada (pregunta #6).
                bunny('Barlow', {
                    alias: 'sans',
                    weights: [400, 500, 600],
                }),
                bunny('Barlow Condensed', {
                    alias: 'display',
                    weights: [500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

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
                // Los 6 archivos (2 familias × 3 pesos) se siguen generando y sirviendo —
                // ningún texto del sitio se queda sin su peso real —, pero solo se
                // PRECARGAN los 2 que se usan arriba del pliegue en cualquier plantilla
                // (cuerpo 400 y encabezados 700): precargar los 6 como recurso de alta
                // prioridad competía por ancho de banda con la imagen del LCP bajo 4G
                // simulada — medido en la línea base, docs/09-rendimiento.md §2/§4
                // ("Element render delay" de la imagen del hero, ~2 s en más de una
                // corrida, coincidiendo con la carga de las 6 fuentes de golpe).
                bunny('Barlow', {
                    alias: 'sans',
                    weights: [400, 500, 600],
                    preload: [{weight: 400}],
                }),
                bunny('Barlow Condensed', {
                    alias: 'display',
                    weights: [500, 600, 700],
                    preload: [{weight: 700}],
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

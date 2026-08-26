import { expect, test, type APIRequestContext } from '@playwright/test';

/**
 * Verificación de contenido (docs/11-qa-testing.md §5) contra el sitio real
 * en desarrollo — no fixtures: rastrea el contenido migrado real de la
 * Fase 5 (16 páginas, 3 noticias, 128 medios) buscando enlaces rotos e
 * imágenes sin `alt`. Corre una sola vez (Desktop Chrome): es un rastreo de
 * HTML devuelto por el servidor, no depende del motor de renderizado.
 *
 * No es un crawl exhaustivo de todo el sitio (eso es trabajo de una
 * herramienta dedicada tipo Screaming Frog, fuera de alcance de esta sesión) —
 * cubre las plantillas principales y sigue sus enlaces internos a un nivel
 * de profundidad, que es donde vive casi todo el contenido real.
 */

const seedPages = [
    '/',
    '/institucion/quienes-somos',
    '/noticias',
    '/contacto',
    '/vida-escolar/comunicados',
    '/vida-escolar/calendario',
    '/vida-escolar/galeria',
    '/documentos',
];

async function collectInternalLinks(request: APIRequestContext, baseURL: string, path: string): Promise<string[]> {
    const response = await request.get(path);
    const html = await response.text();
    const hrefMatches = [...html.matchAll(/href="(\/[^"#]*)"/g)].map((m) => m[1]);

    return [...new Set(hrefMatches)].filter((href) =>
        !href.startsWith('/panel-dante-2026')
        && !href.startsWith('/storage/')
        && !href.startsWith('/build/'));
}

test('rastreo interno: sin enlaces rotos en las plantillas principales', async ({ request, baseURL }, testInfo) => {
    test.skip(testInfo.project.name !== 'Desktop Chrome', 'rastreo de HTML del servidor, no depende del motor de navegador');

    const visited = new Set<string>();
    const broken: Array<{ from: string; to: string; status: number }> = [];

    for (const seed of seedPages) {
        const links = await collectInternalLinks(request, baseURL!, seed);

        for (const link of [seed, ...links]) {
            if (visited.has(link)) continue;
            visited.add(link);

            const response = await request.get(link);
            if (!response.ok()) {
                broken.push({ from: seed, to: link, status: response.status() });
            }
        }
    }

    expect(broken, JSON.stringify(broken, null, 2)).toEqual([]);
});

test('las imágenes de contenido de las plantillas principales tienen alt (vacío o roto = defecto)', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'Desktop Chrome', 'una sola pasada alcanza, el DOM de imágenes no cambia por motor');

    const missingAlt: Array<{ pagina: string; src: string | null }> = [];
    const brokenImages: Array<{ pagina: string; src: string }> = [];

    for (const path of seedPages) {
        await page.goto(path);

        const images = page.locator('img');
        const count = await images.count();

        for (let i = 0; i < count; i++) {
            const img = images.nth(i);
            const alt = await img.getAttribute('alt');
            const src = await img.getAttribute('src');

            // `alt=""` es válido para imágenes decorativas (WCAG 1.1.1) — el
            // defecto real es que el atributo no exista.
            if (alt === null) {
                missingAlt.push({ pagina: path, src });
            }

            if (src) {
                const naturalWidth = await img.evaluate((el: HTMLImageElement) => el.naturalWidth).catch(() => 0);
                if (naturalWidth === 0) {
                    brokenImages.push({ pagina: path, src });
                }
            }
        }
    }

    expect(missingAlt, JSON.stringify(missingAlt, null, 2)).toEqual([]);
    expect(brokenImages, JSON.stringify(brokenImages, null, 2)).toEqual([]);
});

test('el pie de página muestra el año actual y el nombre de la institución consistente', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'Desktop Chrome', 'una sola pasada alcanza');

    await page.goto('/');

    const currentYear = new Date().getFullYear().toString();
    await expect(page.locator('footer')).toContainText(currentYear);
    await expect(page.locator('footer')).toContainText('Dante Alighieri');
});

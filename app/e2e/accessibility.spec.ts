import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

/**
 * Accesibilidad WCAG 2.1 AA (docs/11-qa-testing.md §3) sobre cada plantilla
 * pública real, contra el contenido de prueba de `dante:e2e-fixtures` y el
 * contenido migrado real de la Fase 5. Solo corre en Desktop Chrome — axe
 * evalúa el DOM renderizado, no el motor del navegador, correrlo en los 5
 * proyectos sería redundante y no agrega cobertura real.
 *
 * `disableRules(['color-contrast'])` NO se usa: si algo rompe contraste, tiene
 * que aparecer como violación real, no silenciarse.
 */

const templates: Array<{ nombre: string; path: string }> = [
    { nombre: 'Inicio', path: '/' },
    { nombre: 'Institucional', path: '/institucion/quienes-somos' },
    { nombre: 'Noticias (listado)', path: '/noticias' },
    { nombre: 'Detalle de noticia', path: '/noticias/la-scuola-dante-alighieri-celebra-su-129-aniversario-con-musica-y-arte' },
    { nombre: 'Contacto', path: '/contacto' },
    { nombre: 'Búsqueda', path: '/buscar?q=dante' },
    { nombre: '404', path: '/esta-pagina-no-existe-qa-e2e' },
    { nombre: 'Comunicados', path: '/vida-escolar/comunicados' },
    { nombre: 'Calendario', path: '/vida-escolar/calendario' },
    { nombre: 'Galería', path: '/vida-escolar/galeria' },
    { nombre: 'Documentos', path: '/documentos' },
];

for (const { nombre, path } of templates) {
    test(`accesibilidad (axe, WCAG 2.1 AA) — ${nombre}`, async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'Desktop Chrome', 'axe corre una sola vez, no por motor de navegador');

        await page.goto(path);

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
            .analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
}

test('panel admin (login) — accesibilidad (axe, WCAG 2.1 AA)', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'Desktop Chrome', 'axe corre una sola vez, no por motor de navegador');

    await page.goto('/panel-dante-2026/login');

    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('panel admin (dashboard autenticado) — accesibilidad (axe, WCAG 2.1 AA)', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'Desktop Chrome', 'axe corre una sola vez, no por motor de navegador');

    await page.goto('/panel-dante-2026/login');
    await page.getByLabel('Correo electrónico').fill('qa.playwright@dante-web.test');
    await page.getByRole('textbox', { name: 'Contraseña' }).fill('PlaywrightQA-2026!');
    await page.getByRole('button', { name: 'Entrar' }).click();
    await expect(page).toHaveURL(/panel-dante-2026$/);

    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/**
 * Navegación completa por teclado (docs/11-qa-testing.md §3, ítem que axe no
 * detecta): el menú móvil se abre, se navega y se cierra con `Esc`, y el foco
 * queda siempre visible.
 */
test.describe('Navegación por teclado', () => {
    test('el menú móvil se abre con el botón, se navega con Tab y se cierra con Escape', async ({ page }, testInfo) => {
        test.skip(!testInfo.project.name.includes('Mobile'), 'el menú hamburguesa solo existe en viewport móvil');

        await page.goto('/');

        const toggle = page.getByRole('button', { name: 'Abrir menú de navegación' });
        await toggle.focus();
        await page.keyboard.press('Enter');

        const mobileNav = page.locator('#mobile-nav');
        await expect(mobileNav).toHaveClass(/is-open/);
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');

        await page.keyboard.press('Escape');
        await expect(mobileNav).not.toHaveClass(/is-open/);
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        // El foco vuelve al botón que abre el menú (no se pierde en el limbo),
        // y el panel cerrado queda fuera del tab order (`inert`, ver
        // site-header.blade.php) — un Tab desde acá no debe caer en sus enlaces.
        await expect(toggle).toBeFocused();
        await expect(mobileNav).toHaveJSProperty('inert', true);
    });

    test('recorre el formulario de contacto solo con Tab y el foco queda visible', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'Desktop Chrome', 'basta un motor de escritorio para probar el orden de tabulación');

        await page.goto('/contacto');

        // Salta directo al primer campo del formulario (evita depender del
        // número exacto de enlaces de header/skip-link, que cambia si se
        // agrega un ítem de menú) y confirma que Tab avanza campo a campo en
        // orden lógico dentro del formulario: Nombre → Teléfono → Email →
        // Mensaje (ver resources/views/partials/form-contact.blade.php).
        await page.getByLabel('Nombre completo').focus();
        await expect(page.getByLabel('Nombre completo')).toBeFocused();

        await page.keyboard.press('Tab');
        await expect(page.getByLabel('Teléfono')).toBeFocused();

        await page.keyboard.press('Tab');
        await expect(page.getByLabel('Email')).toBeFocused();

        await page.keyboard.press('Tab');
        await expect(page.getByLabel('Mensaje')).toBeFocused();
    });

    test('el enlace "saltar al contenido" funciona con teclado', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'Desktop Chrome', 'basta un motor de escritorio');

        await page.goto('/');
        await page.keyboard.press('Tab');

        const skipLink = page.getByRole('link', { name: /saltar al contenido/i });
        await expect(skipLink).toBeFocused();

        await page.keyboard.press('Enter');
        await expect(page.locator('#contenido, main')).toBeFocused();
    });
});

/**
 * Zoom al 200 % (docs/11-qa-testing.md §3, ítem que axe no detecta): sustituto
 * razonable dentro del entorno disponible. La propiedad CSS `zoom` NO es
 * equivalente al zoom real del navegador (Ctrl+/pellizco): magnifica el
 * render pero no reduce los píxeles CSS disponibles en el viewport, así que
 * no reproduce el reflow real y da falsos positivos (probado y descartado).
 * El equivalente correcto de "200 % de zoom sobre un viewport de 1280 px" es
 * un viewport de 640 px de ancho a escala 1:1 — mismos píxeles CSS
 * disponibles que ve un usuario con zoom real (WCAG 1.4.10 Reflow: sin
 * scroll horizontal ni contenido recortado).
 */
test.describe('Zoom al 200% (sustituto: viewport de 640px, equivalente en píxeles CSS)', () => {
    for (const { nombre, path } of templates.slice(0, 5)) {
        test(`${nombre} — sin scroll horizontal al equivalente de 200% de zoom`, async ({ page }, testInfo) => {
            test.skip(testInfo.project.name !== 'Desktop Chrome', 'basta un motor de escritorio para el reflow');

            await page.setViewportSize({ width: 640, height: 900 });
            await page.goto(path);

            const hasHorizontalScroll = await page.evaluate(
                () => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
            );

            expect(hasHorizontalScroll, `${nombre} genera scroll horizontal al equivalente de 200% de zoom`).toBe(false);
        });
    }
});

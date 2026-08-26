import { expect, test } from '@playwright/test';
import { clickNavLink, resetFormsThrottle, waitPastHoneypotThreshold } from './helpers';

/**
 * Las 5 tareas críticas de docs/02-ux-arquitectura-informacion.md §6, en
 * móvil y escritorio (los 5 proyectos de playwright.config.ts cubren ambos
 * + los 3 motores). Corre contra el entorno local con los datos de prueba
 * de `php artisan dante:e2e-fixtures` (contenido marcado `[QA E2E]`).
 *
 * Hallazgo real corregido durante esta fase: la página /admisiones no tenía
 * ningún enlace hacia los formularios de pre-inscripción — se agregaron dos
 * bloques CTA reales apuntando a cada sede (ver docs/11-qa-testing.md §6).
 */

test.describe('Tarea 1 — Quiero inscribir a mi hijo/a', () => {
    test.beforeEach(() => resetFormsThrottle());

    test('desde el inicio, llega al formulario de pre-inscripción de Asunción y lo envía', async ({ page }) => {
        await page.goto('/');
        // El CTA del hero es el único visible fuera del menú móvil (que
        // repite el mismo texto en su pie, oculto salvo que esté abierto).
        await page.locator('a.hero-cta', { hasText: 'Quiero inscribir a mi hijo/a' }).click();
        await expect(page).toHaveURL(/\/admisiones$/);
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible();

        await page.getByRole('link', { name: 'Pre-inscribirme en Asunción' }).click();
        await expect(page).toHaveURL(/\/admisiones\/pre-inscripcion$/);

        const unique = Date.now();
        await page.getByLabel('Nombre completo').fill('Familia de Prueba QA');
        await page.getByLabel('Email').fill(`qa.${unique}@example.com`);
        await page.getByLabel('Teléfono').fill('0981123456');
        await page.getByLabel('Sede').selectOption('asuncion');
        await waitPastHoneypotThreshold(page);
        await page.getByRole('button', { name: 'Enviar pre-inscripción' }).click();

        await expect(page.getByText('Recibimos tu pre-inscripción')).toBeVisible();
    });
});

test.describe('Tarea 2 — Quiero ver el calendario académico', () => {
    test('desde el inicio, llega al calendario y ve el evento de prueba', async ({ page }) => {
        await page.goto('/');
        // El pie tiene un único enlace a Calendario académico (accesos
        // secundarios), sin duplicado móvil/escritorio como el header.
        await page.locator('footer').getByRole('link', { name: 'Calendario académico' }).click();
        await expect(page).toHaveURL(/\/vida-escolar\/calendario$/);
        await expect(page.getByRole('heading', { level: 1, name: 'Calendario académico' })).toBeVisible();
        await expect(page.getByText('[QA E2E] Evento de prueba')).toBeVisible();
    });
});

test.describe('Tarea 3 — Quiero contactar a la secretaría', () => {
    test.beforeEach(() => resetFormsThrottle());

    test('desde el inicio, llega a Contacto y envía el formulario', async ({ page }) => {
        await page.goto('/');
        // El pie enlaza Contacto una sola vez (sin duplicado móvil/escritorio).
        await page.locator('footer').getByRole('link', { name: 'Contacto', exact: true }).click();
        await expect(page).toHaveURL(/\/contacto$/);
        await expect(page.getByRole('heading', { level: 1, name: 'Contacto' })).toBeVisible();

        // Datos de contacto directo visibles sin pasar por el formulario.
        await expect(page.getByRole('link', { name: 'colegioasu@dante.edu.py' }).first()).toBeVisible();

        const unique = Date.now();
        await page.getByLabel('Nombre completo').fill('Vecino de Prueba QA');
        await page.getByLabel('Email').fill(`qa.contacto.${unique}@example.com`);
        await page.getByLabel('Mensaje').fill('Consulta de prueba generada por el E2E de QA.');
        await waitPastHoneypotThreshold(page);
        await page.getByRole('button', { name: 'Enviar mensaje' }).click();

        await expect(page.getByText('Gracias por escribirnos')).toBeVisible();
    });

    test('no envía el formulario si faltan los campos obligatorios', async ({ page }) => {
        await page.goto('/contacto');
        await page.getByRole('button', { name: 'Enviar mensaje' }).click();

        // Los campos obligatorios tienen `required` en el HTML: el propio
        // navegador bloquea el envío antes de llegar al servidor. La
        // validación del lado servidor (Form Request) queda cubierta por
        // Pest (`tests/Feature/PublicFormsTest.php`), que sí puede saltearse
        // la validación del navegador.
        await expect(page).toHaveURL(/\/contacto$/);
        await expect(page.getByText('Gracias por escribirnos')).not.toBeVisible();
    });
});

test.describe('Tarea 4 — Quiero ver noticias y comunicados recientes', () => {
    test('ve el listado de noticias y entra al detalle de una', async ({ page }) => {
        await page.goto('/');
        // El pie enlaza Noticias una sola vez (sin duplicado móvil/escritorio).
        await page.locator('footer').getByRole('link', { name: 'Noticias', exact: true }).click();
        await expect(page).toHaveURL(/\/noticias$/);

        const firstNews = page.locator('a[href*="/noticias/"]').first();
        const href = await firstNews.getAttribute('href');
        await firstNews.click();

        expect(href).toBeTruthy();
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    });

    test('ve los comunicados recientes, incluido el de prueba', async ({ page }) => {
        await page.goto('/');
        // "Comunicados" solo existe en el submenú del header (no en el pie),
        // que sí tiene versión móvil/escritorio distinta — usa el helper.
        await clickNavLink(page, 'Comunicados', 'Vida escolar');
        await expect(page).toHaveURL(/\/vida-escolar\/comunicados$/);
        await expect(page.getByText('[QA E2E] Comunicado de prueba')).toBeVisible();
    });
});

test.describe('Tarea 5 — Quiero descargar un documento institucional', () => {
    test('llega al listado de documentos y descarga el documento de prueba', async ({ page }) => {
        await page.goto('/documentos');
        await expect(page.getByRole('heading', { level: 1, name: 'Documentos' })).toBeVisible();

        const link = page.getByRole('link', { name: /QA E2E.*Documento de prueba/ });
        await expect(link).toBeVisible();

        const href = await link.getAttribute('href');
        expect(href).toContain('/storage/media/');

        const response = await page.request.get(href!);
        expect(response.status()).toBe(200);
        expect(response.headers()['content-type']).toContain('pdf');
    });
});

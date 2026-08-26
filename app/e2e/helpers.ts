import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import type { Page } from '@playwright/test';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * El header tiene dos menús paralelos en el DOM (`.desktop-nav` y
 * `#mobile-nav`, ver `resources/views/components/site-header.blade.php`),
 * uno oculto por CSS según el viewport (`min-width: 1024px`,
 * `resources/css/app.css`). Sin esto, `getByRole('link', ...).first()`
 * puede resolver al enlace oculto y colgar el test en los proyectos móviles.
 *
 * `parentLabel` es obligatorio cuando el enlace vive dentro de un submenú
 * (ej. "Comunicados" bajo "Vida escolar"): en escritorio el submenú se abre
 * con `:hover`/`:focus-within` (CSS), en móvil con un botón que despliega un
 * acordeón — cada uno necesita un paso previo distinto.
 */
export async function clickNavLink(page: Page, name: string, parentLabel?: string): Promise<void> {
    const viewport = page.viewportSize();
    const isMobile = (viewport?.width ?? 1280) < 1024;

    if (isMobile) {
        await page.getByRole('button', { name: 'Abrir menú de navegación' }).click();

        if (parentLabel) {
            await page.locator('#mobile-nav').getByRole('button', { name: parentLabel }).click();
        }

        await page.locator('#mobile-nav').getByRole('link', { name }).click();

        return;
    }

    const desktopNav = page.locator('.desktop-nav');

    if (parentLabel) {
        await desktopNav.getByText(parentLabel, { exact: true }).hover();
    }

    await desktopNav.getByRole('link', { name }).click();
}

/**
 * `spatie/laravel-honeypot` (`config/honeypot.php`, `amount_of_seconds => 1`)
 * descarta en silencio cualquier envío que llegue antes de 1 segundo desde
 * que se cargó el formulario — y responde con una página en blanco
 * (`BlankPageResponder`), sin ningún mensaje. Hallazgo real de Fase 9: los
 * tests que llenan el formulario con `.fill()` (casi instantáneo) y envían
 * en el acto quedan por debajo de ese segundo y el propio honeypot los trata
 * como bot, aunque sean un envío legítimo. Se documenta también como riesgo
 * real para usuarios humanos con autocompletado del navegador (ver
 * docs/11-qa-testing.md §6).
 */
export async function waitPastHoneypotThreshold(page: Page): Promise<void> {
    await page.waitForTimeout(1500);
}

/**
 * Los formularios públicos comparten un límite de 5 envíos por hora por IP
 * (`throttle:5,60,forms`, `routes/web.php`). Correr la suite completa contra
 * los 5 proyectos (2 envíos reales por proyecto — pre-inscripción y contacto)
 * agota ese límite antes de terminar, con un 429 real que no tiene nada que
 * ver con el código bajo prueba. Se limpia la caché entre proyectos — nunca
 * en producción, es lo mismo que hace `php artisan dante:e2e-fixtures`.
 */
export function resetFormsThrottle(): void {
    const phpBinary = process.env.PLAYWRIGHT_PHP_BINARY
        ?? '/Users/leonshy/Library/Application Support/Herd/bin/php83';
    const appDir = path.resolve(__dirname, '..');

    execSync(`"${phpBinary}" artisan cache:clear`, { cwd: appDir, stdio: 'ignore' });
}

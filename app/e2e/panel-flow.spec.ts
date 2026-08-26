import { expect, test } from '@playwright/test';

/**
 * Tarea crítica #6 (docs/11-qa-testing.md §2): flujo completo del panel —
 * login → crear página → publicar → verla en el sitio público. Usa
 * exclusivamente el usuario de prueba creado por
 * `php artisan dante:e2e-fixtures` (`qa.playwright@dante-web.test`), nunca
 * credenciales reales del cliente. 2FA está desactivado en este entorno de
 * desarrollo (`DANTE_REQUIRE_2FA=false`, ver docs/10-seguridad.md §2) — el
 * login de este test no pasa por el desafío TOTP.
 */
test('login en el panel, crea una página, la publica y la ve en el sitio público', async ({ page }) => {
    const adminPath = '/panel-dante-2026';
    const uniqueTitle = `Página de prueba QA E2E ${Date.now()}`;

    await page.goto(`${adminPath}/login`);
    await page.getByLabel('Correo electrónico').fill('qa.playwright@dante-web.test');
    await page.getByRole('textbox', { name: 'Contraseña' }).fill('PlaywrightQA-2026!');
    await page.getByRole('button', { name: 'Entrar' }).click();

    await expect(page).toHaveURL(new RegExp(`${adminPath}$`));

    await page.goto(`${adminPath}/pages/create`);
    const slugField = page.getByRole('textbox', { name: 'Dirección web de la página (URL)' });

    await page.getByRole('textbox', { name: 'Título' }).first().fill(uniqueTitle);
    // El slug se autogenera del título al perder el foco (`afterStateUpdated`
    // en `PageForm`) — se espera ese resultado en vez de pisarlo a mano, para
    // no pelear con la ida y vuelta de Livewire por una carrera de eventos.
    await slugField.click();
    await expect(slugField).not.toHaveValue('');
    const actualSlug = await slugField.inputValue();

    await page.getByLabel('Sección del menú').selectOption('general');
    await page.getByLabel('Estado').selectOption('published');
    await page.getByRole('button', { name: 'Crear', exact: true }).click();

    await expect(page.getByText('Creado').or(page.getByText('creada'))).toBeVisible({ timeout: 10000 });

    const publicResponse = await page.request.get(`/${actualSlug}`);
    expect(publicResponse.status()).toBe(200);

    await page.goto(`/${actualSlug}`);
    await expect(page.getByRole('heading', { level: 1, name: uniqueTitle })).toBeVisible();
});

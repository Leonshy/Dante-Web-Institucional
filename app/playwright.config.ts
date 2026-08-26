import { defineConfig, devices } from '@playwright/test';

/**
 * Configuración de Playwright para los recorridos E2E de la Fase 9 (QA).
 *
 * Corre contra el entorno local de Herd/Valet (`dante-web.test`), NUNCA contra
 * producción. Usa exclusivamente los datos de prueba creados por
 * `php artisan dante:e2e-fixtures` (usuario `qa.playwright@dante-web.test`,
 * contenido marcado `[QA E2E]`) — nunca credenciales reales del cliente.
 *
 * Los proyectos Desktop Chrome/Firefox/Safari + Mobile Chrome/Safari cubren a
 * la vez el checklist de "compatibilidad" (motores reales de Chromium,
 * Firefox y WebKit — sustituto razonable de Safari de escritorio, ver nota en
 * docs/11-qa-testing.md §4) y el de "móvil y escritorio" de las 5 tareas
 * críticas. Falta la verificación en dispositivos físicos reales (iOS/Android
 * reales), documentada como pendiente explícito.
 */
export default defineConfig({
    testDir: './e2e',
    globalSetup: './e2e/global-setup.ts',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }]],
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://dante-web.test',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'Desktop Chrome', use: { ...devices['Desktop Chrome'] } },
        { name: 'Desktop Firefox', use: { ...devices['Desktop Firefox'] } },
        { name: 'Desktop Safari (WebKit)', use: { ...devices['Desktop Safari'] } },
        { name: 'Mobile Chrome (Android)', use: { ...devices['Pixel 7'] } },
        { name: 'Mobile Safari (iOS)', use: { ...devices['iPhone 14'] } },
    ],
});

import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Antes de correr los E2E, se asegura que exista el contenido y el usuario
 * de prueba (`php artisan dante:e2e-fixtures`, idempotente, marcado `[QA E2E]`).
 * Nunca toca datos reales del cliente y se niega a correr si el entorno de la
 * app fuera `production` (el propio comando lo verifica de nuevo, ver
 * `app/Console/Commands/SeedE2eFixturesCommand.php`).
 */
export default function globalSetup(): void {
    const phpBinary = process.env.PLAYWRIGHT_PHP_BINARY
        ?? '/Users/leonshy/Library/Application Support/Herd/bin/php83';
    const appDir = path.resolve(__dirname, '..');

    execSync(`"${phpBinary}" artisan dante:e2e-fixtures`, {
        cwd: appDir,
        stdio: 'inherit',
    });
}

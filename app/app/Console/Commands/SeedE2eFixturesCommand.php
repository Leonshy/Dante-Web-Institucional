<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\User;
use App\Services\Media\MediaUploadService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de prueba para los recorridos E2E de Playwright (Fase 9, docs/11-qa-testing.md).
 *
 * Solo crea contenido marcado explícitamente como "[QA E2E]" para poder identificarlo
 * y borrarlo con `--clean`. Nunca se corre contra producción (se aborta si
 * `APP_ENV=production`) y el usuario de prueba nunca usa credenciales reales del cliente.
 */
class SeedE2eFixturesCommand extends Command
{
    protected $signature = 'dante:e2e-fixtures {--clean : Borra los datos de prueba en vez de crearlos}';

    protected $description = 'Crea (o limpia) los datos mínimos para los recorridos E2E de Playwright, solo en entornos no productivos';

    private const MARKER = '[QA E2E]';

    private const TEST_EMAIL = 'qa.playwright@dante-web.test';

    private const TEST_PASSWORD = 'PlaywrightQA-2026!';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('dante:e2e-fixtures no puede correr en production.');

            return self::FAILURE;
        }

        if ($this->option('clean')) {
            return $this->clean();
        }

        // Los formularios públicos tienen `throttle:5,60` (docs/10-seguridad.md §6).
        // Sin esto, corridas repetidas de E2E (o pruebas manuales previas en el
        // mismo entorno) dejan el límite agotado y los recorridos de "Contacto"
        // y "Pre-inscripción" fallan por un 429 que no tiene nada que ver con el
        // código bajo prueba. Solo se limpia la caché completa (no hay datos
        // sensibles en ella) y nunca corre en producción (guard de arriba).
        Cache::flush();

        return $this->seed();
    }

    private function seed(): int
    {
        $this->call('db:seed', ['--class' => PermissionSeeder::class]);

        $user = User::query()->updateOrCreate(
            ['email' => self::TEST_EMAIL],
            ['name' => 'QA Playwright', 'password' => Hash::make(self::TEST_PASSWORD), 'is_active' => true],
        );

        if (! $user->hasRole('administrador')) {
            $user->syncRoles(['administrador']);
        }

        if (! Announcement::query()->whereJsonContains('title->es', self::MARKER.' Comunicado de prueba')->exists()) {
            Announcement::query()->create([
                'title' => ['es' => self::MARKER.' Comunicado de prueba'],
                'content' => ['es' => '<p>Comunicado generado para los recorridos E2E de Playwright.</p>'],
                'audience' => 'toda-la-comunidad',
                'published_at' => now(),
                'status' => 'published',
            ]);
        }

        if (! CalendarEvent::query()->whereJsonContains('title->es', self::MARKER.' Evento de prueba')->exists()) {
            CalendarEvent::query()->create([
                'title' => ['es' => self::MARKER.' Evento de prueba'],
                'starts_at' => now()->addDays(5),
                'level' => 'todo-el-colegio',
                'status' => 'published',
            ]);
        }

        if (! Document::query()->whereJsonContains('title->es', self::MARKER.' Documento de prueba')->exists()) {
            $tempPath = tempnam(sys_get_temp_dir(), 'dante-qa-e2e-').'.pdf';
            file_put_contents($tempPath, $this->minimalPdfContents());

            $file = new UploadedFile($tempPath, 'reglamento-qa.pdf', 'application/pdf', null, true);
            $media = app(MediaUploadService::class)->upload($file, 'qa-e2e', 'Documento de prueba QA E2E');

            Document::query()->create([
                'title' => ['es' => self::MARKER.' Documento de prueba'],
                'media_id' => $media->id,
                'site' => 'ambas',
                'published_at' => now(),
                'is_current' => true,
                'status' => 'published',
            ]);
        }

        $this->info('Fixtures de E2E listos. Usuario de prueba: '.self::TEST_EMAIL);

        return self::SUCCESS;
    }

    /**
     * Un PDF de una sola página, válido de verdad (no solo bytes con la extensión
     * correcta) — así `finfo` lo reconoce como `application/pdf` real.
     */
    private function minimalPdfContents(): string
    {
        return <<<'PDF'
        %PDF-1.4
        1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
        2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
        3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj
        xref
        0 4
        0000000000 65535 f
        trailer<</Size 4/Root 1 0 R>>
        startxref
        0
        %%EOF
        PDF;
    }

    private function clean(): int
    {
        User::query()->where('email', self::TEST_EMAIL)->delete();
        Announcement::query()->whereJsonContains('title->es', self::MARKER.' Comunicado de prueba')->delete();
        CalendarEvent::query()->whereJsonContains('title->es', self::MARKER.' Evento de prueba')->delete();
        Document::query()->whereJsonContains('title->es', self::MARKER.' Documento de prueba')->each(function (Document $document): void {
            $document->file?->delete();
            $document->delete();
        });

        $this->info('Fixtures de E2E eliminados.');

        return self::SUCCESS;
    }
}

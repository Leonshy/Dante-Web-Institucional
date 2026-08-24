<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportRedirectsCommand extends Command
{
    protected $signature = 'dante:import-redirects {csv=docs/redirecciones-301.csv}';

    protected $description = 'Carga el mapa de redirecciones 301 desde el CSV de la Fase 1 (idempotente)';

    public function handle(): int
    {
        $path = base_path('../'.ltrim($this->argument('csv'), '/'));

        if (! File::exists($path)) {
            $path = base_path($this->argument('csv'));
        }

        if (! File::exists($path)) {
            $this->error("No se encontró el archivo: {$path}");

            return self::FAILURE;
        }

        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));

        $count = 0;

        foreach ($rows as $row) {
            if (count($row) < 2 || $row[0] === '') {
                continue;
            }

            $data = array_combine($header, $row);

            Redirect::query()->updateOrCreate(
                ['from_path' => rtrim($data['url_vieja'], '/') ?: '/'],
                [
                    'to_path' => $data['url_nueva'],
                    'status_code' => (int) ($data['tipo'] ?? 301),
                    'is_active' => true,
                ],
            );

            $count++;
        }

        $this->info("Redirecciones cargadas: {$count}");

        return self::SUCCESS;
    }
}

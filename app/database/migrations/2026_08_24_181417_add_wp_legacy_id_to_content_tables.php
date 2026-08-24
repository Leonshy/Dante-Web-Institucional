<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clave de correspondencia con el WordPress viejo (`dante_wp_legacy`), usada
     * por `artisan dante:migrate-wp` (Fase 5, docs/07-migracion-wordpress.md) para
     * que la migración sea idempotente: `updateOrCreate` por este ID en vez de
     * duplicar registros cada vez que se corre el comando. Nunca se usa para nada
     * más que trazabilidad — no es una FK real hacia la base legacy (que es de
     * solo lectura y se elimina del entorno una vez verificada la migración).
     */
    public function up(): void
    {
        foreach (['pages', 'posts', 'categories', 'media', 'documents'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('wp_legacy_id')->nullable()->after('id');
                $blueprint->unique('wp_legacy_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['pages', 'posts', 'categories', 'media', 'documents'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropUnique("{$table}_wp_legacy_id_unique");
                $blueprint->dropColumn('wp_legacy_id');
            });
        }
    }
};

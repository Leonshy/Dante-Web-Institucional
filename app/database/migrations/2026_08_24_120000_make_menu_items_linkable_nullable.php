<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `morphs('linkable', ...)` en la migración original crea columnas NOT NULL,
     * pero el modelo de datos (docs/05 §2) define el enlace a Page/Post como
     * opcional: un ítem de menú puede ser un enlace manual (`url`) sin apuntar
     * a ningún contenido. Se corrige acá en vez de editar la migración original
     * porque ya corrió en entornos existentes.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('linkable_type')->nullable()->change();
            $table->unsignedBigInteger('linkable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('linkable_type')->nullable(false)->change();
            $table->unsignedBigInteger('linkable_id')->nullable(false)->change();
        });
    }
};

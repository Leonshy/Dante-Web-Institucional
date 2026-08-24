<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // "Comunicados" — tipo de contenido nuevo, pedido por el cliente
    // (docs/02-ux-arquitectura-informacion.md §7, docs/01 §E pregunta #5).
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('title');
            $table->json('content');
            $table->date('published_at');
            $table->date('valid_until')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->enum('audience', ['toda-la-comunidad', 'asuncion', 'fernando-de-la-mora'])
                ->default('toda-la-comunidad');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

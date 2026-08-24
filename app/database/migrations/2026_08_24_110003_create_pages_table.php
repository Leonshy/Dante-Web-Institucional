<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Adapta el patrón de `pages` de IPG (docs/01 §A.3): se reemplaza el campo `section`
    // fijo por jerarquía real (parent_id) según docs/02-ux-arquitectura-informacion.md §7,
    // y se agregan campos traducibles ES/IT (ADR-002).
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('seo_image_id')->nullable()->constrained('media')->nullOnDelete();

            $table->json('title'); // traducible
            $table->string('slug')->unique();
            $table->string('template')->default('default');
            $table->json('blocks')->nullable(); // constructor de bloques, ver docs/05 §2
            $table->enum('site_section', [
                'institucion', 'oferta-educativa', 'admisiones', 'vida-escolar', 'general',
            ])->default('general');
            $table->enum('site', ['asuncion', 'fernando-de-la-mora', 'ambas'])->nullable();

            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('is_indexable')->default(true);

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'site_section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};

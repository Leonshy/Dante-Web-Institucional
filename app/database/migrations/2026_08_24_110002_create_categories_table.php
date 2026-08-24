<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Taxonomía polimórfica de IPG (docs/01 §A.4), con CRUD real en el panel
    // (en IPG solo se cargaba por seeder) y nombre/slug traducibles.
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // news, document, gallery
            $table->json('name'); // traducible ES/IT
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

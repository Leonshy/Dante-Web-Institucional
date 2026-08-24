<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete(); // el archivo

            $table->json('title');
            $table->json('description')->nullable();
            $table->enum('site', ['asuncion', 'fernando-de-la-mora', 'ambas'])->default('ambas');
            $table->date('published_at')->nullable();
            $table->boolean('is_current')->default(true); // vigente / vencido
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

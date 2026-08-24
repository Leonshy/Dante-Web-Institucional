<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Se copia tal cual el modelo de `posts` de IPG (docs/01 §A.4), con campos ES/IT.
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('seo_image_id')->nullable()->constrained('media')->nullOnDelete();

            $table->json('title');
            $table->string('slug')->unique();
            $table->json('excerpt');
            $table->json('content');

            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->boolean('is_indexable')->default(true);

            $table->boolean('is_featured')->default(false);
            $table->date('published_at')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('title');
            $table->json('description')->nullable();
            $table->date('event_date');
            $table->enum('site', ['asuncion', 'fernando-de-la-mora', 'ambas'])->default('ambas');
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gallery_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_media');
        Schema::dropIfExists('galleries');
    }
};

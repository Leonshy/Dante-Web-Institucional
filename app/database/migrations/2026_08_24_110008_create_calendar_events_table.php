<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('related_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->foreignId('related_announcement_id')->nullable()->constrained('announcements')->nullOnDelete();

            $table->json('title');
            $table->json('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->enum('level', [
                'inicial', 'primaria', 'secundaria', 'instituto-de-idiomas', 'todo-el-colegio',
            ])->default('todo-el-colegio');
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};

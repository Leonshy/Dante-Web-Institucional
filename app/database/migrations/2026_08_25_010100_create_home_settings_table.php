<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_settings', function (Blueprint $table) {
            $table->id();
            $table->json('hero_slides')->nullable();
            $table->json('stats')->nullable();
            $table->json('sections')->nullable();
            $table->json('cta_title')->nullable();
            $table->json('cta_text')->nullable();
            $table->json('cta_button_label')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Patrón heredado tal cual de IPG (docs/01-analisis-descubrimiento.md §A.4):
    // clave/valor/tipo/grupo, cacheado en App\Models\SiteSetting.
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, image, boolean, json
            $table->string('group')->default('general'); // general, contact, social, seo, integrations, idioma
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

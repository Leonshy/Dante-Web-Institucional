<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla central de medios, patrón de IPG (docs/01 §A.4), reforzado con lo que a IPG
    // le falta: alt text, reprocesamiento (conversions) y marca de sanitización de SVG.
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // nombre original, solo para mostrar
            $table->string('file_name'); // nombre aleatorio en disco
            $table->string('mime_type');
            $table->string('path');
            $table->string('disk')->default('media'); // fuera de la raíz pública servida directo
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('image'); // image, document, video
            $table->string('alt')->nullable(); // alt text — obligatorio para imágenes en el panel
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->string('folder')->nullable()->default('general');
            $table->json('conversions')->nullable(); // rutas de variantes webp/avif/responsive
            $table->boolean('svg_sanitized')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

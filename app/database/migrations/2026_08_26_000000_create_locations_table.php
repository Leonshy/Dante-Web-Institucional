<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de contacto por sede (Asunción, Fernando de la Mora, Instituto de
 * Lengua Italiana), administrables desde el panel — reemplaza lo que hoy
 * está escrito a mano en `contact/show.blade.php` y `site-footer.blade.php`.
 * No confundir con el campo `site` (enum) que ya usan Page/Document/etc.
 * para clasificar contenido por sede: acá se administra el CONTACTO de la
 * sede en sí, no a qué sede pertenece un contenido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_email')->nullable();
            $table->string('administrative_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->text('schedule')->nullable();
            $table->text('maps_embed_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

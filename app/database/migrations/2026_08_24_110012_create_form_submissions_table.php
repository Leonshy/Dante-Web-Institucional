<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Adapta `contacts` de IPG (docs/01 §A.4): se quitan los campos de cotización
    // (específicos de venta) y se agrega `type` para distinguir contacto / pre-inscripción,
    // y `read_at`/`status` para el flujo de gestión en el panel.
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['contacto', 'pre_inscripcion'])->default('contacto');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('site', ['asuncion', 'fernando-de-la-mora'])->nullable(); // solo pre-inscripción
            $table->text('message')->nullable();
            $table->enum('status', ['nuevo', 'leido', 'respondido', 'archivado'])->default('nuevo');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reemplaza el 2FA por app autenticadora (TOTP, `Filament\Auth\MultiFactor\App`)
 * por 2FA por código de un solo uso enviado por email
 * (`Filament\Auth\MultiFactor\Email\EmailAuthentication`, nativo de Filament 5)
 * — pedido explícito del cliente: cada usuario decide si lo activa, sin
 * depender de que tenga una app autenticadora instalada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes']);
            $table->boolean('has_email_authentication')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_email_authentication');
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 7 (rendimiento, docs/09-rendimiento.md §6): índices sobre columnas que
     * de verdad se filtran/ordenan hoy en controllers públicos — no especulativos.
     * `posts`/`pages` ya tenían índice compuesto desde su creación; acá se cubren
     * los huecos reales encontrados al revisar `AnnouncementController`,
     * `GalleryController` y `HomeController` (`is_featured_home`).
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // HomeController::index() filtra por is_featured_home + status.
            $table->index(['is_featured_home', 'status'], 'pages_is_featured_home_status_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            // AnnouncementController::index() y HomeController::index() filtran por
            // status y ordenan por is_pinned + published_at.
            $table->index(['status', 'is_pinned', 'published_at'], 'announcements_status_pinned_published_index');
        });

        Schema::table('galleries', function (Blueprint $table) {
            // GalleryController::index() y HomeController::index() filtran por
            // status y ordenan por event_date.
            $table->index(['status', 'event_date'], 'galleries_status_event_date_index');
        });

        Schema::table('documents', function (Blueprint $table) {
            // DocumentController::index() filtra por status + is_current y ordena
            // por published_at.
            $table->index(['status', 'is_current', 'published_at'], 'documents_status_current_published_index');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex('pages_is_featured_home_status_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('announcements_status_pinned_published_index');
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropIndex('galleries_status_event_date_index');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_status_current_published_index');
        });
    }
};

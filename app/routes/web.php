<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/noticias', [PostController::class, 'index'])->name('posts.index');
Route::get('/noticias/{slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');

Route::get('/contacto', [ContactController::class, 'show'])->name('contact.show');

Route::get('/vida-escolar/comunicados', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/vida-escolar/calendario', [CalendarEventController::class, 'index'])->name('calendar.index');
Route::get('/vida-escolar/galeria', [GalleryController::class, 'index'])->name('galleries.index');

// Formularios públicos — honeypot + rate limiting (ausentes en IPG, docs/01 §A.3).
Route::middleware(['honeypot', 'throttle:5,1'])->group(function () {
    Route::post('/contacto', [FormSubmissionController::class, 'contact'])->name('forms.contact');
    Route::post('/admisiones/pre-inscripcion', [FormSubmissionController::class, 'preRegistration'])->name('forms.pre-registration');
});

// Buscador interno — solo lectura, con rate limiting (docs/05 §8). Negocia
// contenido: JSON para consumo programático (tests, fetch), HTML para
// navegación normal (ver App\Http\Controllers\SearchController).
Route::middleware('throttle:30,1')->get('/buscar', [SearchController::class, 'index'])->name('search.index');

// Catch-all de páginas públicas (institucionales / landings de sección) — el
// middleware de redirecciones corre antes (bootstrap/app.php). Va al final
// para no interceptar las rutas específicas de arriba.
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-\/]+')
    ->name('pages.show');

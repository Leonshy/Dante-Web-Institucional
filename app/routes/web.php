<?php

use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Formularios públicos — honeypot + rate limiting (ausentes en IPG, docs/01 §A.3).
Route::middleware(['honeypot', 'throttle:5,1'])->group(function () {
    Route::post('/contacto', [FormSubmissionController::class, 'contact'])->name('forms.contact');
    Route::post('/admisiones/pre-inscripcion', [FormSubmissionController::class, 'preRegistration'])->name('forms.pre-registration');
});

// Buscador interno — solo lectura, con rate limiting (docs/05 §8). La UI
// pública que consume este JSON llega en la Fase 4 (frontend).
Route::middleware('throttle:30,1')->get('/buscar', [SearchController::class, 'index'])->name('search.index');

// Catch-all de páginas públicas — el middleware de redirecciones corre antes
// (bootstrap/app.php). El renderizado real de la plantilla de página llega
// en la Fase 4 (frontend); por ahora responde 404 si no hay redirección.
Route::fallback(function () {
    abort(404);
});

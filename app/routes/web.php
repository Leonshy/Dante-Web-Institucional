<?php

use App\Http\Controllers\FormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Formularios públicos — honeypot + rate limiting (ausentes en IPG, docs/01 §A.3).
Route::middleware(['honeypot', 'throttle:5,1'])->group(function () {
    Route::post('/contacto', [FormSubmissionController::class, 'contact'])->name('forms.contact');
    Route::post('/admisiones/pre-inscripcion', [FormSubmissionController::class, 'preRegistration'])->name('forms.pre-registration');
});

// Catch-all de páginas públicas — el middleware de redirecciones corre antes
// (bootstrap/app.php). El renderizado real de la plantilla de página llega
// en la Fase 4 (frontend); por ahora responde 404 si no hay redirección.
Route::fallback(function () {
    abort(404);
});

<?php

namespace App\Http\Controllers;

use App\Actions\Forms\StoreFormSubmission;
use App\Http\Requests\ContactFormRequest;
use App\Http\Requests\PreRegistrationFormRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller fino — la lógica va en Actions (CLAUDE.md §7).
 * Rate limiting via `throttle` en routes/web.php — ausente en IPG (docs/01 §A.3).
 */
class FormSubmissionController extends Controller
{
    public function contact(ContactFormRequest $request, StoreFormSubmission $action): RedirectResponse
    {
        $action->handle('contacto', $request->validated(), $request->ip());

        return back()->with('status', 'Gracias por escribirnos. Te vamos a responder a la brevedad.');
    }

    public function preRegistration(PreRegistrationFormRequest $request, StoreFormSubmission $action): RedirectResponse
    {
        $action->handle('pre_inscripcion', $request->validated(), $request->ip());

        return back()->with('status', 'Recibimos tu pre-inscripción. Nos vamos a comunicar para coordinar los siguientes pasos.');
    }
}

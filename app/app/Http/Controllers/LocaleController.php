<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        // Si alguien pide "it" con el italiano apagado (enlace viejo en caché,
        // manipulación manual de la URL), se ignora en silencio y se vuelve
        // a la página anterior — no tiene sentido un error acá.
        if ($locale === 'es' || ($locale === 'it' && SiteSetting::italianEnabled())) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}

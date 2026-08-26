<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact.show', [
            'locations' => Location::query()->active()->orderBy('sort_order')->get(),
        ]);
    }
}

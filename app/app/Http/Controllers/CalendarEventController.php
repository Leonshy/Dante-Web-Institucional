<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\View\View;

class CalendarEventController extends Controller
{
    public function index(): View
    {
        $events = CalendarEvent::query()
            ->where('status', 'published')
            ->orderBy('starts_at')
            ->get();

        return view('calendar.index', compact('events'));
    }
}

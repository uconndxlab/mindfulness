<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EventsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    public function index()
    {
        return view('admin.events');
    }

    public function exportEvents()
    {
        return Excel::download(new EventsExport, 'events.xlsx');
    }
}

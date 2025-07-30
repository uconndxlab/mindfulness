<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\EventsExport;
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

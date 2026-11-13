<?php

namespace App\Http\Controllers\Admin;

use App\Exports\NotesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class NoteController extends Controller
{
    public function index()
    {
        return view('admin.notes');
    }

    public function exportNotes()
    {
        return Excel::download(new NotesExport, 'journals.xlsx');
    }
}

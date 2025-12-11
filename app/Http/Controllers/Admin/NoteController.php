<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\NotesExport;
use Maatwebsite\Excel\Facades\Excel;

class NoteController extends Controller
{
    public function index()
    {
        return view('admin.notes');
    }
    
    public function exportNotes() {
        return Excel::download(new NotesExport, 'journals.xlsx');
    }
}

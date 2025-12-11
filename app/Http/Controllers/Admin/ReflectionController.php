<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ReflectionsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReflectionController extends Controller
{
    public function index()
    {
        return view('admin.reflections');
    }

    public function exportReflections()
    {
        return Excel::download(new ReflectionsExport, 'reflections.xlsx');
    }
}

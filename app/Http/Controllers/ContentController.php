<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function contentUploadPage() {
        return view('admin.contentUpload');
    }
}

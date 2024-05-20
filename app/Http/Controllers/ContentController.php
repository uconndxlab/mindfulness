<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function contentUploadPage() {
        //adjust navbars
        $showBackBtn = true;
        $hideBottomNav = true;
        $hideProfileLink = true;
        return view('admin.contentUpload', compact('showBackBtn', 'hideBottomNav', 'hideProfileLink'));
    }

    public function uploadContent() { 

    }
}

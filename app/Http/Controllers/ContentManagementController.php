<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;

class ContentManagementController extends Controller
{


    public function __construct(PageNavController $pageNavController)
    {
        $this->pageNavController = $pageNavController;
    }
    public function adminPage() {
        return view('admin.home');
    }
}

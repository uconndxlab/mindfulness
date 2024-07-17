<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Day;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Module;

class ContentManagementController extends Controller
{


    public function __construct(PageNavController $pageNavController)
    {
        $this->pageNavController = $pageNavController;
    }

    //NAVIGATION
    public function adminPage() {
        $modules = Module::all();
        return view('admin.home', compact('modules'));
    }

    //MODULES
    public function showModule($module_id) {
        return 'showModule';
    }

    public function editModule($module_id) {
        return 'editModule';       
    }

    public function createModule() {
        return 'createModule';
    }

    public function storeModule() {
        return 'storeModule';
    }

    public function deleteModule($module_id) {
        try {
            DB::statement('PRAGMA foreign_keys = ON;');
            // Find the module to delete
            $module = Module::findOrFail($module_id);
    
            // Delete related records in related_table
            Day::where('module_id', $module_id)->delete();
    
            // Now delete the module
            $module->delete();
            DB::statement('PRAGMA foreign_keys = OFF;');
    
            return redirect()->route('modules.index')->with('success', 'Module deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete module: ' . $e->getMessage()]);
        }
    }

    //DAYS
    //ACTIVITIES
}

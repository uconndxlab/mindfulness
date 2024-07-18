<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Session;

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
            $module = Module::findOrFail($module_id);
            $activity = Activity::findOrFail(getConfig('first_activity_id'));
            if ($activity->day && $module == $activity->day->module) {
                //change the first activity
                $next_module = Module::where('order', $module->order+1)->first();
                $new_first_activity = Activity::whereHas('day', function($query) use ($next_module) {
                    $query->where('module_id', $next_module->id);
                })->orderBy('order', 'asc')->first();
                updateConfig('first_activity_id', $new_first_activity->id);
                foreach (User::all() as $user) {
                    unlockFirst($user->id);
                }
                Session::flush();
            }
            //set all days and modules inside to "deleted"
            foreach ($module->days as $day) {
                foreach ($day->activities as $activity) {
                    $activity->deleted = true;
                    $activity->save();
                }
                $day->deleted = true;
                $day->save();
            }

            $module->delete();
            return redirect()->back()->with('success', 'Module deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete module: ' . $e->getMessage()]);
        }
    }

    //DAYS
    //ACTIVITIES
}

<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\Module;
use App\Models\Day;
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
        $lost_days = Day::where('deleted', true)->get();
        return view('admin.home', compact('modules', 'lost_days'));
    }

    //MODULES
    public function showModule($module_id) {
        $module = Module::findOrFail($module_id);
        $lost_activities = Activity::where('deleted', true)->get();
        return view('admin.module', compact('module', 'lost_activities'));
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
                //will flush everyones session
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
    public function showDay($day_id) {
        return 'showDay';
    }

    public function editDay($day_id) {
        return 'editDay';       
    }

    public function createDay() {
        return 'createDay';
    }

    public function storeDay() {
        return 'storeDay';
    }
    public function deleteDay($day_id) {
        return 'deleteDay';
    }
    //ACTIVITIES
    public function showActivity($activity_id) {
        return 'showActivity';
    }

    public function editActivity($activity_id) {
        return 'editActivity';       
    }

    public function createActivity() {
        return 'createActivity';
    }

    public function storeActivity() {
        return 'storeActivity';
    }

    public function deleteActivity($activity_id) {
        return 'deleteActivity';
    }
}

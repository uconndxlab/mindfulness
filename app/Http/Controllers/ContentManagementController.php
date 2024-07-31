<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\Module;
use App\Models\Day;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class ContentManagementController extends Controller
{


    public function __construct(PageNavController $pageNavController)
    {
        $this->pageNavController = $pageNavController;
    }

    //NAVIGATION
    public function indexModule() {
        $title = "Admin Content Management";
        $head = "Modules";
        $back_route = route('account');
        $big_list = Module::all();
        $item_type = 'module';
        $lost_list = Day::where('deleted', true)->get();
        $lost_type = 'day';
        return view('admin.index', compact('title', 'head', 'back_route', 'big_list', 'item_type', 'lost_list', 'lost_type'));
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
    public function indexDay(Request $request) {
        $module = Module::findOrFail($request->module_id);

        $title = $module->name.": Days";
        $head = $module->name.": Days";
        $back_route = route('module.index');
        $big_list = $module->days;
        $item_type = 'day';
        $lost_list = Activity::where('deleted', true)->get();
        $lost_type = 'activity';
        return view('admin.index', compact('title', 'head', 'back_route', 'big_list', 'item_type', 'lost_list', 'lost_type'));
    }

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
    public function indexActivity(Request $request) {
        $day = Day::findOrFail($request->day_id);

        $title = $day->name.": Activities";
        $head = $day->name.": Activities";
        $back_route = route('day.index', ['module_id' => $day->module_id]);
        $big_list = $day->activities;
        $item_type = 'activity';
        return view('admin.index', compact('title', 'head', 'back_route', 'big_list', 'item_type'));
    }

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

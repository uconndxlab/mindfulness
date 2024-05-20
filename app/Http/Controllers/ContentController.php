<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PageNavController;

class ContentController extends Controller
{
    protected $pageNavController;

    public function __construct(PageNavController $pageNavController)
    {
        $this->pageNavController = $pageNavController;
    }

    public function adminPage() {
        //page for adding and editing lessons

        $fromAdmin = true;
        $showBackBtn = true;
        $hideBottomNav = true;
        $hideProfileLink = true;

        //get list of modules
        $modules = $this->pageNavController->getModulesList();
        //set back_route - hardcode???
        Session::put("admin_back_route", '/profile');
        //using modified explore page
        return view("explore.home", compact('modules', 'fromAdmin', 'showBackBtn', 'hideBottomNav', 'hideProfileLink'));
    }

    public function newLessonPage() {
        //show page for creating a new lesson

        //adjust navbars
        $showBackBtn = true;
        $hideBottomNav = true;
        $hideProfileLink = true;

        Session::put("admin_back_route", '/admin');

        $modules = Module::orderBy('module_number', 'asc')->get();

        return view('admin.lessonUpload', compact('showBackBtn', 'hideBottomNav', 'hideProfileLink', 'modules'));
    }

    public function showLessonPage($lessonId) {
        //show page for editing a lesson

        //adjust navbars
        $showBackBtn = true;
        $hideBottomNav = true;
        $hideProfileLink = true;

        Session::put("admin_back_route", '/admin');

        $modules = Module::orderBy('module_number', 'asc')->get();
        $lesson = Lesson::select('id', 'title', 'module_id', 'file_path', 'description')->find($lessonId);

        return view('admin.lessonUpload', compact('showBackBtn', 'hideBottomNav', 'hideProfileLink', 'modules', 'lesson'));
    }

    public function storeLesson(Request $request) {
        //store a new lesson
        try {
            $request->validate([
                //update validation for moduleid
                'title' => ['required', 'string', 'max:255'],
                'module' => ['required', 'string', 'in:relax,compassion,other'],
                'description' => ['string', 'max:1027'],
                'file'=> ['extensions:m4a,mp3,mp4'],
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }
}

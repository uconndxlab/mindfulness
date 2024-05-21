<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PageNavController;
use Illuminate\Support\Facades\Storage;

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

    public function newLessonPage(Request $request) {
        //show page for creating a new lesson

        //adjust navbars
        $showBackBtn = true;
        $hideBottomNav = true;
        $hideProfileLink = true;

        //tracking module that was clicked
        $moduleId = $request->moduleId;

        Session::put("admin_back_route", '/admin');

        $modules = Module::orderBy('module_number', 'asc')->get();

        return view('admin.lessonUpload', compact('showBackBtn', 'hideBottomNav', 'hideProfileLink', 'modules', 'moduleId'));
    }

    public function storeLesson(Request $request) {
        //store a new lesson
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'module' => ['required', 'exists:modules,id'],
                'description' => ['nullable', 'string', 'max:1027'],
                'file'=> ['nullable', 'mimes:mp4,mp3,wav,ogg', 'max:40960'],
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        //upload file
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('content');
        }

        //update module lesson count
        $module = Module::find($request->module);
        $module->lesson_count = Lesson::where('module_id', $module->id)->count() + 1;
        $module->save();

        //create lesson
        $lesson = Lesson::create([
            'title' => $request->title,
            'module_id' => $request->module,
            'lesson_number' => $module->lesson_count,
            'description' => $request->description,
            'file_path' => $filePath,
        ]);

        return redirect()->route('admin.browse')->with('success', 'Lesson created successfully!');
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

    public function updateLesson(Request $request, $lessonId) {
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'module' => ['required', 'exists:modules,id'],
                'description' => ['nullable', 'string', 'max:1027'],
                'file'=> ['nullable', 'mimes:mp4,mp3,wav,ogg', 'max:40960'],
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $lesson = Lesson::find($lessonId);
        $lesson->title = $request->title;
        if ($lesson->module_id != $request->module) {
            //lesson count on old module
            $module = Module::find($lesson->module_id);
            $module->lesson_count--;
            $module->save();
            
            //adjusting lesson numbers
            $lessonsAfter = Lesson::where('module_id', $lesson->module_id)
                                    ->where('lesson_number', '>', $lesson->lesson_number)
                                    ->get();
            foreach ($lessonsAfter as $lessonAfter) {
                $lessonAfter->lesson_number--;
                $lessonAfter->save();
            }
            
            //assign lesson to module
            $lesson->module_id = $request->module;
            $module = Module::find($lesson->module_id);
            $module->lesson_count++;
            $lesson->lesson_number = $module->lesson_count;
            $module->save();
        }
        $lesson->description = $request->description;

        //file check
        $sameFile = false;
        if ($request->hasFile('file')) {
            $newFile = $request->file('file');
            $newFileHash = hash_file('md5', $newFile->getRealPath());
            //if path saved in db
            if ($lesson->file_path) {
                $currentFilePath = storage_path('app/'.$lesson->file_path);
                //if file exists
                if (file_exists($currentFilePath)) {
                    $currentFileHash = hash_file('md5', $currentFilePath);
                    //if file is not the same
                    if ($newFileHash !== $currentFileHash) {
                        //delete old
                        Storage::disk()->delete($lesson->file_path);
                    }
                    else {
                        $sameFile = true;
                    }
                }
            }
            if (!$sameFile) {
                //save new
                $newFilePath = $newFile->store('content');
                $lesson->file_path = $newFilePath;
            }
        }

        //DELETE FILE if not passed in?

        $lesson->save();
        return redirect()->route('admin.browse')->with('success', 'Lesson updated successfully.');
    }

    public function deleteLesson($lessonId) {
        $lesson = Lesson::find($lessonId);
        //delete content
        if ($lesson->file_path) {
            if (file_exists(storage_path('app/'.$lesson->file_path))) {
                Storage::disk()->delete($lesson->file_path);
            }
        }
        $module = Module::find($lesson->module_id);

        //adjusting lesson numbers
        $lessonsAfter = Lesson::where('module_id', $module->id)
                                ->where('lesson_number', '>', $lesson->lesson_number)
                                ->get();
        foreach ($lessonsAfter as $lessonAfter) {
            $lessonAfter->lesson_number--;
            $lessonAfter->save();
        }

        $lesson->delete();
        //adjust module
        $module->lesson_count--;
        $module->save();

        return redirect()->route('admin.browse')->with('success', 'Lesson deleted.');
    }
}

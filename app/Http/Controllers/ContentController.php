<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PageNavController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    protected $pageNavController;

    public function __construct(PageNavController $pageNavController)
    {
        $this->pageNavController = $pageNavController;
    }

    public function handleQuiz(Request $request, $lessonId) {
        $request->validate([
            'quiz_question' => ['required', 'string', 'max:255'],
            'quiz_correct_answer' => ['required', 'integer'],
        ]);

        $optionsFeedback = [];
        $index = 1;
        
        // $answer = "none";
        while ($request->has("option_$index")) {
            //assigning the correct answer
            // if ($index == $request->quiz_correct_answer) {
            //     $answer = $request->input("option_$index");
            // }
            $optionsFeedback[] = [
                'option' => $request->input("option_$index"),
                'feedback' => $request->input("feedback_$index"),
            ];
            $index++;
        }
        
        //update the quiz or create one depending on if found
        Quiz::updateOrCreate(
            ['lesson_id' => $lessonId],
            [
                'question' => $request->quiz_question,
                'options_feedback' => json_encode($optionsFeedback),
                'correct_answer' => $request->quiz_correct_answer,
            ]
        );
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
                'sub_header' => ['nullable', 'string', 'max:255'],
                'module' => ['required', 'exists:modules,id'],
                'description' => ['nullable', 'string', 'max:1027'],
                'file'=> ['nullable', 'mimes:mp4,mp3,wav,ogg', 'max:40960'],
                'end_behavior' => ['required', 'in:none,quiz,journal'],
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        //transaction to ensure both the lesson and quiz go through
        DB::beginTransaction();
        try {
            //upload file
            $filePath = null;
            $file_name = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('content', 'public');
                $file_name = $request->file('file')->getClientOriginalName();
            }
    
            //update module lesson count
            $module = Module::find($request->module);
            $module->lesson_count = Lesson::where('module_id', $module->id)->count() + 1;
            $module->save();
    
            //create lesson
            $lesson = Lesson::create([
                'title' => $request->title,
                'sub_header' => $request->sub_header,
                'module_id' => $request->module,
                'lesson_number' => $module->lesson_count,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_name' => $file_name,
                'end_behavior' => $request->end_behavior
            ]);
    
            if ($request->end_behavior == 'quiz') {
                $this->handleQuiz($request, $lesson->id);
            }

            DB::commit();
            return redirect()->route('admin.browse')->with('success', 'Lesson created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the lesson and quiz.'])->withInput();
        }
    }

    public function showLessonPage($lessonId) {
        //show page for editing a lesson

        //adjust navbars
        $showBackBtn = true;
        $hideBottomNav = true;
        $hideProfileLink = true;

        Session::put("admin_back_route", '/admin');

        $modules = Module::orderBy('module_number', 'asc')->get();
        $lesson = Lesson::find($lessonId);
        $quiz = null;
        if ($lesson->end_behavior == 'quiz') {
            $quiz = Quiz::where('lesson_id', $lesson->id)->first();
        }

        return view('admin.lessonUpload', compact('showBackBtn', 'hideBottomNav', 'hideProfileLink', 'modules', 'lesson', 'quiz'));
    }

    public function updateLesson(Request $request, $lessonId) {
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'sub_header' => ['nullable', 'string', 'max:255'],
                'module' => ['required', 'exists:modules,id'],
                'description' => ['nullable', 'string', 'max:1027'],
                'file'=> ['nullable', 'mimes:mp4,mp3,wav,ogg', 'max:40960'],
                'end_behavior' => ['required', 'in:none,quiz,journal']
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }


        //transaction to ensure both the lesson and quiz go through
        DB::beginTransaction();
        try {
            $lesson = Lesson::find($lessonId);
            $lesson->title = $request->title;
            $lesson->sub_header = $request->sub_header;
            $lesson->end_behavior = $request->end_behavior;
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
                    $currentFilePath = storage_path('app/public/'.$lesson->file_path);
                    //if file exists
                    if (file_exists($currentFilePath)) {
                        $currentFileHash = hash_file('md5', $currentFilePath);
                        //if file is not the same
                        if ($newFileHash !== $currentFileHash) {
                            //delete old
                            Storage::disk()->delete('public/'.$lesson->file_path);
                        }
                        else {
                            $sameFile = true;
                        }
                    }
                }
                if (!$sameFile) {
                    //same file
                    $newFilePath = $newFile->store('content', 'public');
                    $lesson->file_path = $newFilePath;
                    $lesson->file_name = $newFile->getClientOriginalName();
                }
            }

            $lesson->save();
            if ($request->end_behavior == 'quiz') {
                $this->handleQuiz($request, $lesson->id);
            }

            DB::commit();
            return redirect()->route('admin.browse')->with('success', 'Lesson updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function deleteLesson($lessonId) {
        $lesson = Lesson::findOrFail($lessonId);
        //delete content
        if ($lesson->file_path) {
            if (file_exists(storage_path('app/public/'.$lesson->file_path))) {
                Storage::disk()->delete('public/'.$lesson->file_path);
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

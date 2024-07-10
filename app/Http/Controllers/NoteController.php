<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'note' => ['required', 'string', 'max:1027'],
                'word_otd' => ['required', 'in:relax,compassion,other'],
            ], [
                'note.required' => 'Please enter a note.',
                'note.string' => 'The note must be a string.',
                'note.max' => 'The note may not be greater than 1027 characters.',
                'word_otd.required' => 'Please select a word of the day.',
                'word_otd.in' => 'Please select a word of the day.',
            ]);
    
            $note = Note::create([
                'note' => $validatedData['note'],
                'word_otd' => $validatedData['word_otd'],
                'user_id' => Auth::id(),
            ]);

            //if submitted note after activity
            if ($request->activity_id) {
                return redirect()->route('explore.activity',  ['activity_id' => $request->activity_id])->with('success', 'Journal submitted!');
            }
    
            return back()->with('success', 'Note saved.');
    
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return 'index';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return 'create';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'note'=> ['required', 'string', 'max:1027'],
            'word_otd'=> ['required', 'in:Relax,Compassion,Other'],
        ]);
        
        $note = Note::create([
            'note' => $request->note,
            'word_otd'=> $request->word_otd,
            'user_id'=> Auth::id(),
        ]);

        return back()->with('success', 'Note saved.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        return 'show';
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        return 'edit';
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

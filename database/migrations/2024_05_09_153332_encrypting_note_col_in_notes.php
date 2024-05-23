<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //encrypting all notes
        $notes = DB::table('notes')->select('id', 'note')->get();

        foreach ($notes as $note) {
            DB::table('notes')->where('id', $note->id)->update(['note' => Crypt::encryptString($note->note)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $notes = DB::table('notes')->select('id', 'note')->get();

        foreach ($notes as $note) {
            DB::table('notes')->where('id', $note->id)->update(['note' => Crypt::decryptString($note->note)]);
        }
    }
};

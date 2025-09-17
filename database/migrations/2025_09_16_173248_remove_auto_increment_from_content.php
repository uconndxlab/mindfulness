<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        
        try {
            // note, this migration might drop the days table...
            $this->recreateTableWithoutAutoIncrement('content');
            $this->recreateTableWithoutAutoIncrement('quizzes');
            $this->recreateTableWithoutAutoIncrement('journals');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To reverse, we'd need to recreate with auto-increment
        // This is complex in SQLite, better to restore from backup
        echo "WARNING: Cannot easily reverse this migration in SQLite. Restore from backup if needed.\n";
    }

    private function recreateTableWithoutAutoIncrement(string $tableName): void
    {
        $tempTableName = $tableName . '_temp';
        
        // get current schema
        $columns = Schema::getColumnListing($tableName);
        $data = DB::table($tableName)->get();
        
        // create new table, without auto-increment
        if ($tableName === 'content') {
            $this->createContentTable($tempTableName);
        } elseif ($tableName === 'quizzes') {
            $this->createQuizzesTable($tempTableName);
        } elseif ($tableName === 'journals') {
            $this->createJournalsTable($tempTableName);
        }
        
        // copy data
        foreach ($data as $row) {
            DB::table($tempTableName)->insert((array) $row);
        }
        
        // drop table, rename temp
        Schema::dropIfExists($tableName);
        Schema::rename($tempTableName, $tableName);
    }

    private function createContentTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->enum('type', ['audio', 'video', 'pdf', 'image']);
            $table->string('file_path');
            $table->json('audio_options')->nullable();
            $table->json('instructions')->nullable();
            $table->timestamps();
        });
    }

    private function createQuizzesTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->json('question_options');
            $table->integer('question_count')->default(0);
            $table->timestamps();
        });
    }

    private function createJournalsTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->string('prompts');
            $table->timestamps();
        });
    }
};

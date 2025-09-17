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
            $this->recreateTableWithoutAutoIncrement('modules');
            $this->recreateTableWithoutAutoIncrement('days');
            $this->recreateTableWithoutAutoIncrement('activities');
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
        if ($tableName === 'activities') {
            $this->createActivitiesTable($tempTableName);
        } elseif ($tableName === 'days') {
            $this->createDaysTable($tempTableName);
        } elseif ($tableName === 'modules') {
            $this->createModulesTable($tempTableName);
        }
        
        // copy data
        foreach ($data as $row) {
            DB::table($tempTableName)->insert((array) $row);
        }
        
        // drop table, rename temp
        Schema::dropIfExists($tableName);
        Schema::rename($tempTableName, $tableName);
    }
    
    private function createActivitiesTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->foreignId('day_id')->nullable()->constrained('days')->onDelete('set null');
            $table->string('title');
            $table->enum('type', ['lesson', 'practice', 'reflection', 'journal'])->nullable();
            $table->integer('time')->nullable();
            $table->string('completion_message')->nullable();
            $table->integer('order');
            $table->boolean('skippable')->default(true);
            $table->boolean('optional')->default(false);
            $table->timestamps();
        });
    }
    
    private function createDaysTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->text('completion_message')->nullable();
            $table->string('media_path')->nullable();
            $table->integer('order');
            $table->boolean('is_check_in')->default(false);
            $table->timestamps();
        });
    }
    
    private function createModulesTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('workbook_path')->nullable();
            $table->integer('order');
            $table->timestamps();
        });
    }
};

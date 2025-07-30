<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Facades\File;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $json = File::get(database_path('data/modules.json'));
        $modules = json_decode($json);

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['id' => $module->id],
                [
                    'name' => $module->name,
                    'description' => $module->description,
                    'workbook_path' => $module->workbook_path,
                    'order' => $module->order,
                ]
            );
        }
    }
}

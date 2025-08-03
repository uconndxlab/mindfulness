<?php

namespace App\Observers;

use App\Models\Module;
use Illuminate\Support\Facades\File;

class ModuleObserver
{
    /**
     * The path to the modules JSON file.
     *
     * @var string
     */
    protected $filePath;

    public function __construct()
    {
        $this->filePath = database_path('data/modules.json');
    }

    /**
     * Handle the Module "created" event.
     */
    public function created(Module $module): void
    {
        $this->updateJsonFile();
    }

    /**
     * Handle the Module "updated" event.
     */
    public function updated(Module $module): void
    {
        $this->updateJsonFile();
    }

    /**
     * Handle the Module "deleted" event.
     */
    public function deleted(Module $module): void
    {
        $this->updateJsonFile();
    }

    /**
     * Handle the Module "restored" event.
     */
    public function restored(Module $module): void
    {
        $this->updateJsonFile();
    }

    /**
     * Handle the Module "force deleted" event.
     */
    public function forceDeleted(Module $module): void
    {
        $this->updateJsonFile();
    }

    /**
     * Reads all modules from the database and writes them to the JSON file.
     */
    protected function updateJsonFile(): void
    {
        // fetch all modules and order by order
        $modules = Module::orderBy('order')->get([
            'id',
            'name',
            'description',
            'workbook_path',
            'order'
        ])->toArray();

        // encode the array into a nicely formatted JSON string
        $jsonContent = json_encode($modules, JSON_PRETTY_PRINT);

        // write the content to the file
        File::put($this->filePath, $jsonContent);
    }
}

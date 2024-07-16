<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Content;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @param bool $examples
     * @return void
     */
    public function run(bool $examples = false): void
    {
        //data to seed depends on the param
        $ftype = $examples ? "Examples.json" : ".json";
        $content = json_decode(file_get_contents(database_path('data/content'.$ftype)), true);
        
        foreach ($content as $item) {
            //setting the completion message for meditation content
            if (isset($item['is_meditation']) && $item['is_meditation']) {
                if (!isset($item['completion_message'])) {
                    $item['completion_message'] = "Congrats on completing the activity!";
                }
                else if ($item['completion_message'] == null) {
                    $item['completion_message'] = "";
                }
                $item['completion_message'] = $item['completion_message']."\nMeditation exercises have been unlocked in the library.";
            }

            if (isset($item['audio_options'])) {
                $item['audio_options'] = json_encode($item['audio_options']);
            }

            Content::create($item);
        }
    }
}

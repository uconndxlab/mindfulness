<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(bool $examples = false): void
    {
        // data to seed depends on the param
        $ftype = $examples ? 'Examples.json' : '.json';
        $content = json_decode(file_get_contents(database_path('data/content'.$ftype)), true);

        foreach ($content as $item) {
            if (isset($item['audio_options'])) {
                $item['audio_options'] = json_encode($item['audio_options']);
            }

            Content::updateOrCreate(
                ['id' => $item['id']],
                [
                    'activity_id' => $item['activity_id'],
                    'type' => $item['type'],
                    'file_path' => $item['file_path'],
                    'audio_options' => $item['audio_options'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                ]
            );
        }
    }
}

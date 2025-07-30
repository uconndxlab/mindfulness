<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;
use Illuminate\Support\Facades\Config;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = json_decode(file_get_contents(database_path('data/faqs.json')), true);
        foreach ($faqs as $item) {
            $item['answer'] = str_replace(
                'config_email', 
                '<a href="mailto:'.Config::get('mail.contact_email').'" class="text-decoration-none">'.Config::get('mail.contact_email').'</a>', 
                $item['answer']
            );
            $item['answer'] = str_replace(
                'config_phone',
                '<a href="tel:'.Config::get('mail.contact_phone').'" class="text-decoration-none">'.formatPhone(Config::get('mail.contact_phone')).'</a>', 
                $item['answer']
            );
            Faq::updateOrCreate(
                ['id' => $item['id']],
                [
                    'question' => $item['question'],
                    'answer' => $item['answer'],
                ]
            );
        }
    }
}

<?php

namespace App\Exports;

use App\Models\QuizAnswers;
use App\Services\QuizAnswerFormatter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReflectionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return QuizAnswers::with([
            'user:id,hh_id',
            'quiz:id,title',
            'activity:id,title',
            'subject'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'User ID',
            'Quiz',
            'Subject',
            'Type',
            'Answers',
            'Created At',
            'Updated At',
        ];
    }

    public function map(mixed $reflection): array
    {
        // get subject name
        $subjectName = '-';
        if ($reflection->subject) {
            if (isset($reflection->subject->title)) {
                $subjectName = 'Activity: ' . $reflection->subject->title;
            } elseif ($reflection->subject instanceof \App\Models\Module) {
                $subjectName = 'Module: ' . $reflection->subject->partName();
            }
        }

        // format reflection type
        $reflectionType = match($reflection->reflection_type) {
            'check_in' => 'Quick Check-In',
            'self_rating' => 'Self Rating',
            default => 'Other',
        };

        // format answers using the formatter
        $formattedAnswers = QuizAnswerFormatter::formatAnswers($reflection->answers);

        return [
            $reflection->user->hh_id ?? '-',
            $reflection->activity->title ?? '-',
            $subjectName,
            $reflectionType,
            $formattedAnswers,
            $reflection->created_at?->format('Y-m-d H:i:s'),
            $reflection->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}



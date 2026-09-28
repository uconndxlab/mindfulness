<?php

namespace App\Exports;

use App\Models\Note;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NotesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Note::with(['user:id,hh_id'])->get();
    }

    public function headings(): array
    {
        return [
            'User ID',
            'Topic',
            'Note',
            'Created At',
            'Updated At',
        ];
    }

    public function map(mixed $note): array
    {
        return [
            $note->user->hh_id ?? 'None',
            $note->topic ? ucfirst(strip_tags(preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $note->topic))) : 'None',
            $note->note ?? 'None',
            $note->created_at?->format('Y-m-d H:i:s'),
            $note->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}


<?php

namespace App\Exports;

use App\Models\EventLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Str;

class EventsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return EventLog::with('causer:id,hh_id', 'subject')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Log Name',
            'Description',
            'Causer Type',
            'Causer ID',
            'Event',
            'Subject Type',
            'Subject ID/Name',
            'Properties',
            'Created At',
            'Updated At',
        ];
    }

    public function map($event): array
    {
        $subjectName = null;
        $subjectType = null;
        if ($event->subject_type) {
            $subjectName = match ($event->subject_type) {
                'App\Models\Activity' => $event->subject->title,
                'App\Models\Day' => $event->subject->name,
                'App\Models\Module' => $event->subject->name,
                default => $event->subject->id,
            };
            $subjectType = Str::afterLast($event->subject_type, '\\');
        }

        return [
            $event->id,
            $event->log_name,
            $event->description,
            Str::afterLast($event->causer_type, '\\'),
            $event->causer->hh_id,
            $event->event,
            $subjectType,
            $subjectName,
            $event->properties,
            $event->created_at,
            $event->updated_at,
        ];
    }
} 
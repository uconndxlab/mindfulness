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
            'User ID',
            'Description',
            'Subject Type',
            'Subject ID/Name',
            'Timestamp (UTC)',
            'Local Timestamp',
            'Log Name',
            'Event',
            'Properties',
        ];
    }

    public function map($event): array
    {
        $subjectName = null;
        $subjectType = null;
        if ($event->subject_type) {
            $subjectType = Str::afterLast($event->subject_type, '\\');

            if ($event->subject) {
                $subjectName = match ($event->subject_type) {
                    'App\Models\Activity' => $event->subject->title,
                    'App\Models\Day' => $event->subject->name,
                    'App\Models\Module' => $event->subject->name,
                    default => $event->subject->id,
                };
            } else {
                $subjectName = $event->subject_id;
            }
        }

        return [
            $event->id,
            $event->causer?->hh_id ?? 'Unknown',
            $event->description,
            $subjectType,
            $subjectName,
            $event->created_at,
            $event->local_timestamp,
            $event->log_name,
            $event->event,
            $event->properties,
        ];
    }
} 
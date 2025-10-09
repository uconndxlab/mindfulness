<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\User;
use Illuminate\Support\Str;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Role',
            'Current Activity',
            'Current Day',
            'Current Part',
            'Last Active (UTC)',
            'Joined (UTC)',
            'Verified',
        ];
    }

    public function map($user): array
    {

        $currentActivity = $user->currentActivity();
        $currentDay = $currentActivity?->day;
        $currentModule = $currentDay?->module;

        return [
            $user->hh_id,
            $user->name,
            $user->email,
            $user->role,
            $currentActivity?->title ?? 'None',
            $currentDay?->name ?? 'None',
            $currentModule ? 'Part '.$currentModule->order.' - '.$currentModule->name : 'None',
            $user->last_active_at,
            $user->created_at,
            $user->email_verified_at ? 'Yes' : 'No',
        ];
    }
} 
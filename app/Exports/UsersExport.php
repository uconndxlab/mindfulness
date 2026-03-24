<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\User;
use App\Enums\MilestoneType;
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
            'Registered',
            'First Activity',
            'Module 1',
            'Module 2',
            'Module 3',
            'Module 4',
            'Current Activity',
            'Current Day',
            'Current Part',
            'Number of Favorites',
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

        $milestones = $user->milestones->keyBy(fn($m) => $m->type->value);

        return [
            $user->hh_id,
            $user->name,
            $user->email,
            $user->role,
            $milestones->has(MilestoneType::Registered->value) 
                ? $milestones->get(MilestoneType::Registered->value)->achieved_at . ' (UTC)'
                : '',
            $milestones->has(MilestoneType::FirstActivity->value) 
                ? $milestones->get(MilestoneType::FirstActivity->value)->achieved_at . ' (UTC)'
                : '',
            $milestones->has(MilestoneType::Module1->value) 
                ? $milestones->get(MilestoneType::Module1->value)->achieved_at . ' (UTC)'
                : '',
            $milestones->has(MilestoneType::Module2->value) 
                ? $milestones->get(MilestoneType::Module2->value)->achieved_at . ' (UTC)'
                : '',
            $milestones->has(MilestoneType::Module3->value) 
                ? $milestones->get(MilestoneType::Module3->value)->achieved_at . ' (UTC)'
                : '',
            $milestones->has(MilestoneType::Module4->value) 
                ? $milestones->get(MilestoneType::Module4->value)->achieved_at . ' (UTC)'
                : '',
            $currentActivity?->title ?? 'None',
            $currentDay?->name ?? 'None',
            $currentModule ? 'Part '.$currentModule->order.' - '.$currentModule->name : 'None',
            $user->favoritedActivities()->count(),
            $user->last_active_at,
            $user->created_at,
            $user->email_verified_at ? 'Yes' : 'No',
        ];
    }
} 
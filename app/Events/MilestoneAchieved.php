<?php

namespace App\Events;

use App\Enums\MilestoneType;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneAchieved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public MilestoneType $type
    ) {}
}

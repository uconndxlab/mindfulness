<?php

namespace App\Events;

use App\Enums\MilestoneType;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneAchieved
{
    use Dispatchable, SerializesModels;

    public User $user;
    public string $milestoneType;

    public function __construct(User $user, MilestoneType $type)
    {
        $this->user = $user;
        $this->milestoneType = $type->value;
    }
}

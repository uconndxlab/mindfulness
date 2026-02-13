<?php

namespace App\Models;

use App\Enums\MilestoneType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMilestone extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'achieved_at',
        'admin_notified_at',
    ];

    protected $casts = [
        'type' => MilestoneType::class,
        'achieved_at' => 'datetime',
        'admin_notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

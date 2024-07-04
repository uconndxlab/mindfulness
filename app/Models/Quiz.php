<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $casts = [
        'options_feedback' => 'json',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}

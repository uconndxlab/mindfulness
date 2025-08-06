<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $casts = [
        'question_options' => 'json',
        'options_feedback' => 'json',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function answers($user_id = null)
    {
        $query = $this->hasMany(QuizAnswers::class);

        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        
        return $query;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswers extends Model
{
    use HasFactory;

    protected $casts = [
        'answers' => 'encrypted:array',
    ];

    protected $table = 'quiz_answers';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'answers',
        'reflection_type',
        'activity_id',
        'average',
        'subject_id',
        'subject_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // the actual activity id (quiz has this too)
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    // what this quiz answer is about (polymorphic relationship) - could be activity or module
    public function subject()
    {
        return $this->morphTo();
    }

    // get checkins 
    public function scopeCheckIns($query)
    {
        return $query->where('reflection_type', 'check_in');
    }

    // get self rating reflections
    public function scopeSelfRatings($query)
    {
        return $query->where('reflection_type', 'self_rating');
    }

    // all survey/slider reflections
    public function scopeReflections($query)
    {
        return $query->whereIn('reflection_type', ['check_in', 'self_rating']);
    }
}

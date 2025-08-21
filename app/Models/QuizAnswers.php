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
        'answers'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}

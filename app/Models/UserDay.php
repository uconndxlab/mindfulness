<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDay extends Model
{
    use HasFactory;

    protected $table = 'user_day';

    protected $fillable = [
        'user_id',
        'day_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Note extends Model
{
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'note',
        'user_id',
        'topic',
        'activity_id'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}

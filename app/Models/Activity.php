<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    public function day()
    {
        return $this->belongsTo(Day::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function content()
    {
        return $this->hasOne(Content::class);
    }
}

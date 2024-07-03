<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    public function week()
    {
        return $this->belongsTo(Week::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}

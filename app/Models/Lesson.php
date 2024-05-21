<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    protected $fillable = [
        'title',
        'module_id',
        'lesson_number',
        'description',
        'file_path',
        'file_name',
        'sub_header',
        'end_behavior'
    ];
}

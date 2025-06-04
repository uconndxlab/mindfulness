<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScormSession extends Model
{
    protected $fillable = [
        'user_id',
        'scorm_package_id',
        'lesson_status',
        'score',
        'suspend_data',
        'lesson_location',
        'cmi_data'
    ];

    protected $casts = [
        'cmi_data' => 'array'
    ];
}

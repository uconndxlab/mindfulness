<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScormPackage extends Model
{
    protected $fillable = [
        'title',
        'type',
        'version',
        'entry_point',
        'status',
        'xapi_activity_id'
    ];

    public function sessions()
    {
        return $this->hasMany(ScormSession::class);
    }
}

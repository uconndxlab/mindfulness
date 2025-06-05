<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Content;

class XapiPackage extends Model
{
    protected $fillable = [
        'title',
        'package_id',
        'entry_point',
        'xapi_activity_id'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'xapi_activity_id');
    }

    public function activity()
    {
        return $this->content->activity;
    }
}

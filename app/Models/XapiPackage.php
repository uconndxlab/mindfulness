<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XapiPackage extends Model
{
    protected $fillable = [
        'title',
        'package_id',
        'entry_point',
        'xapi_activity_id'
    ];
}

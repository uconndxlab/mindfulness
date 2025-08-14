<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $casts = [
        'message' => 'encrypted',
        'name' => 'encrypted',
        'email' => 'encrypted',
        'subject' => 'encrypted',
    ];

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message'
    ];
}

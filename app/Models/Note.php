<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'note',
        'user_id',
        'word_otd',
    ];

    public function setNoteAttribute($value)
    {
        $this->attributes['note'] = Crypt::encryptString($value);
    }

    public function getNoteAttribute($value)
    {
        return Crypt::decryptString($value);
    }
}

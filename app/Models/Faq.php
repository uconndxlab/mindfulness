<?php

namespace App\Models;

use App\Enums\FaqCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'order',
    ];

    protected $casts = [
        'category' => FaqCategory::class,
    ];

    public function scopeByCategory($query, FaqCategory $category)
    {
        return $query->where('category', $category->value);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

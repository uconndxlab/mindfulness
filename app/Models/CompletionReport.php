<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompletionReport extends Model
{
    protected $fillable = [
        'user_id',
        'pdf_path',
        'last_sent_at',
    ];

    protected $casts = [
        'last_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function pdfPathForHhId(string $hhId): string
    {
        return 'completion-reports/' . hash('sha256', $hhId) . '.pdf';
    }
}

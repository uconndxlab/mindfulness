<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'invited_by',
        'status',
        'expires_at',
        'used_at',
        'registered_user_id',
        'last_sent_at',
        'resend_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    // relationships
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    // scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('expires_at', '<', Carbon::now());
            });
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now());
    }

    // methods
    public function isValid(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function markAsUsed(User $user): void
    {
        $this->update([
            'status' => 'accepted',
            'used_at' => Carbon::now(),
            'registered_user_id' => $user->id,
        ]);
    }

    public function revoke(): void
    {
        $this->update([
            'status' => 'revoked',
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    public function markAsPending(): void
    {
        $time = Carbon::now();
        $this->update([
            'status' => 'pending',
            'token' => self::generateUniqueToken(),
            'expires_at' => $time->addDays((int) config('invitations.expiration_days', 7)),
            'last_sent_at' => $time,
            'resend_count' => $this->resend_count + 1,
        ]);
    }

    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }
}

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method static findOrFail(int $user_id)
 */

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'voiceId',
        'last_active_at',
        'lock_access'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function progress_activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public function quiz_answers($quiz_id = null)
    {
        $query = $this->hasMany(QuizAnswers::class);

        if ($quiz_id) {
            $query->where('quiz_id', $quiz_id);
        }

        return $query;
    }
}

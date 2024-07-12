<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'points', 'last_bonus_date',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_bonus_date' => 'date',
        'password' => 'hashed',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // 新しいメソッドを追加（オプション）
    public function canClaimLoginBonus()
    {
        return !$this->last_bonus_date || Carbon::parse($this->last_bonus_date)->isYesterday();
    }
    public function updateLoginStreak()
    {
        $now = Carbon::now();
        
        if ($this->last_login_at === null) {
            $this->login_streak = 1;
        } elseif ($now->diffInDays($this->last_login_at) == 1) {
            $this->login_streak++;
        } elseif ($now->diffInDays($this->last_login_at) > 1) {
            $this->login_streak = 1;
        }

        $this->last_login_at = $now;
        $this->save();
    }
}
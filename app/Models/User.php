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
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // アクセサ：登録日を取得
    public function getRegisteredAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    // アクセサ：最終ログイン日時を取得
    public function getLastLoginAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    // ミューテータ：最終ログイン日時を設定
    public function setLastLoginAtAttribute($value)
    {
        $this->attributes['last_login_at'] = $value ? Carbon::parse($value) : null;
    }
}
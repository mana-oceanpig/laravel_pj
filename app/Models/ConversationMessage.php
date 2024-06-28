<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id', 'role_id', 'message', 'summary', 'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
    
}

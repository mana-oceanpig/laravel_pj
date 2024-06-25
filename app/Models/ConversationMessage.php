<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id', 'role_id', 'message', 'is_hidden',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}

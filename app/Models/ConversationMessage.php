<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'external_id', 'message', 'summary', 'is_hidden', 'role_id'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
}
?>

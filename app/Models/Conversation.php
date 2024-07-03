<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Conversation extends Model
{
    use HasFactory;
    
    
    protected $dates = [
    'created_at',
    'updated_at',
    'last_activity_at',
    ];

    const STATUS_IN_PROGRESS = 'inProgress';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELED = 'canceled';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = ['user_id', 'status', 'last_activity_at'];

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function checkAndUpdateExpired()
    {
        if ($this->status === self::STATUS_IN_PROGRESS) {
            $inactiveTime = $this->freshTimestamp()->diffInMinutes($this->last_activity_at);
            if ($inactiveTime >= 30) {
                $this->update(['status' => self::STATUS_EXPIRED]);
            }
        }
    }

    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function markAsCanceled()
    {
        $this->update(['status' => self::STATUS_CANCELED]);
    }
    
}
?>

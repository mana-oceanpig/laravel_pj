<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'summary', 'serotonin', 'dopamine', 'oxytocin', 'recommendations', 'pdf_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

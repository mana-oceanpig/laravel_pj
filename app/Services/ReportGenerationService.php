<?php

namespace App\Services;

use App\Models\User;
use App\Models\Report;

class ReportGenerationService
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateReport(User $user)
    {
        $conversations = $user->conversations()->orderBy('created_at', 'desc')->take(10)->get();
        $conversationText = $conversations->map(function ($conversation) {
            return "User: {$conversation->message}\nAI: {$conversation->ai_response}";
        })->implode("\n\n");

        $analysis = $this->aiService->analyzeMentalState($conversationText);

        return Report::create([
            'user_id' => $user->id,
            'summary' => $analysis['summary'],
            'serotonin' => $analysis['serotonin'],
            'dopamine' => $analysis['dopamine'],
            'oxytocin' => $analysis['oxytocin'],
            'recommendations' => $analysis['recommendations'],
        ]);
    }
}
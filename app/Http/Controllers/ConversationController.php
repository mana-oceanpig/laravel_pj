<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\AIService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $conversations = auth()->user()->conversations()->orderBy('created_at', 'desc')->get();
        return view('conversation.index', compact('conversations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $aiResponse = $this->aiService->getResponse($request->message);

        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'ai_response' => $aiResponse,
        ]);

        return response()->json([
            'message' => $request->message,
            'ai_response' => $aiResponse,
        ]);
    }
}
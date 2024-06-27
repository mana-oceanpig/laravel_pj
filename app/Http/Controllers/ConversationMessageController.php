<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Models\Conversation;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ConversationMessageController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }
    
    public function handle(Request $request)
    {
        try {
            $validatedData = $this->validateRequest($request);
            $conversation = $this->getOrCreateConversation();
            $summarizedText = $this->summarizeText($validatedData['text']);
            $responseText = $this->generateResponse($summarizedText);
            $conversationMessage = $this->saveConversationMessage($conversation->id, $validatedData['text'], $summarizedText, $responseText);
            
            return $this->successResponse($responseText, $summarizedText, $conversationMessage);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    private function validateRequest(Request $request)
    {
        return $request->validate([
            'text' => 'required|string|max:1000',
            'conversation_id' => 'sometimes|exists:conversations,id',
        ]);
    }

    private function getOrCreateConversation()
    {
        $conversationId = request('conversation_id');
        if ($conversationId) {
            $conversation = Conversation::findOrFail($conversationId);
            if ($conversation->user_id !== Auth::id()) {
                throw new Exception('Unauthorized access to conversation');
            }
        } else {
            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'external_id' => uniqid('conv_', true),
            ]);
        }
        return $conversation;
    }

    private function summarizeText($text)
    {
        return strlen($text) > 100 ? $this->openAIService->summarizeText($text) : $text;
    }

    private function generateResponse($text)
    {
        return $this->openAIService->generateResponse($text);
    }

    private function saveConversationMessage($conversationId, $text, $summarizedText, $responseText)
    {
        $userMessage = ConversationMessage::create([
            'conversation_id' => $conversationId,
            'role_id' => config('conversation.user_role_id'),
            'message' => $text,
            'summarized_text' => $summarizedText,
        ]);

        $aiMessage = ConversationMessage::create([
            'conversation_id' => $conversationId,
            'role_id' => config('conversation.ai_role_id'),
            'message' => $responseText,
        ]);

        return [$userMessage, $aiMessage];
    }

    private function successResponse($responseText, $summarizedText, $conversationMessages)
    {
        return response()->json([
            'response' => $responseText,
            'summarizedText' => $summarizedText,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'conversation_id' => $conversationMessages[0]->conversation_id,
            'user_message_id' => $conversationMessages[0]->id,
            'ai_message_id' => $conversationMessages[1]->id,
        ]);
    }

    private function errorResponse(Exception $e)
    {
        Log::error('Conversation processing error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to process conversation'], 500);
    }

    public function getConversationHistory($conversationId = null)
    {
        $query = Conversation::with('messages')->where('user_id', Auth::id());
        
        if ($conversationId) {
            $query->where('id', $conversationId);
        }

        $conversations = $query->get();
        
        $history = $conversations->map(function($conversation) {
            return [
                'conversation_id' => $conversation->id,
                'external_id' => $conversation->external_id,
                'messages' => $conversation->messages->map(function($message) {
                    return [
                        'id' => $message->id,
                        'role' => $message->role_id == config('conversation.user_role_id') ? 'user' : 'ai',
                        'text' => $message->message,
                        'summarized_text' => $message->summarized_text,
                        'timestamp' => $message->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        });

        return response()->json($history);
    }
}
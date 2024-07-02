<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationMessagesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        $message = new ConversationMessage();
        $message->conversation_id = $request->input('conversation_id');
        $message->message = $request->input('message');
        $message->save();

        // 会話の最終アクティビティを更新
        $conversation = Conversation::find($request->input('conversation_id'));
        $conversation->last_activity_at = now();
        $conversation->save();

        return redirect()->back()->with('status', 'メッセージが送信されました。');
    }
}

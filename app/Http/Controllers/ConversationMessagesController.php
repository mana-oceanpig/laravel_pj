<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenAI;

class ConversationMessagesController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPEN_AI_SECRET_KEY'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string',
        ]);
        $user_id = Auth::id();
        $role_id = 1; // Assuming this is the user's role ID

        // ユーザーのメッセージを保存
        $message = new ConversationMessage();
        $message->conversation_id = $request->input('conversation_id');
        $message->message = $request->input('message');
        $message->role_id = 1; // ユーザーのロールID
        $message->save();

        // 会話の最終アクティビティを更新
        $conversation = Conversation::find($request->input('conversation_id'));
        $conversation->last_activity_at = now();
        $conversation->save();

        // Send message to OpenAI and handle response
        $response = $this->sendMessageToChatGPT($conversation, $message->message);

        // Store OpenAI's response
        $responseMessage = new ConversationMessage();
        $responseMessage->conversation_id = $conversation->id;
        $responseMessage->message = $response;
        $responseMessage->role_id = 2; // Assuming this is the agent's role ID
        $responseMessage->save();

        // Check if the response indicates the end of conversation
        if (Str::contains(strtolower($response), 'bye')) {
            $conversation->agent_status = 'reacted';
            $conversation->save();
        }
        // Save summary if it's a summary message
        if ($responseMessage->summary) {
            $conversationMessage = new ConversationMessage();
            $conversationMessage->conversation_id = $conversation->id;
            $conversationMessage->message = $response;
            $conversationMessage->role_id = 2; // ChatGPTのロールID
            $conversationMessage->summary = true; // サマリーであることを示すフラグ
            $conversationMessage->save();
        }

        return redirect()->back()->with('status', 'メッセージが送信されました。');
    }

    private function sendMessageToChatGPT($conversation, $message)
    {
        $assistantId = env('OPEN_AI_ASSISTANT_ID');

        // スレッドの取得または作成
        if (!$conversation->thread_id) {
            $thread = $this->client->threads()->create([]);
            $conversation->thread_id = $thread->id;
            $conversation->save();
        }

        // メッセージをスレッドに追加
        $this->client->threads()->messages()->create($conversation->thread_id, [
            'content' => $message,
            'role' => 'user',
        ]);

        // ジョブをスレッドに追加して実行
        $run = $this->client->threads()->runs()->create($conversation->thread_id, [
            'assistant_id' => $assistantId,
        ]);

        // ジョブが完了するまで待機
        $this->waitUntilRunCompleted($conversation->thread_id, $run->id);


        // ChatGPTのレスポンスを取得
        $messages = $this->client->threads()->messages()->list(
            $conversation->thread_id,
            [
                'order' => 'desc',
                'limit' => 1,
            ]
        );

        foreach ($messages->data as $message) {
            foreach ($message->content as $content) {
                if ($content['text'] !== null) {
                    return $content['text']['value'];
                }
            }
        }

        return '';
    }
    private function waitUntilRunCompleted($threadId, $runId)
    {
        $count = 0;
        do {
            sleep(3);
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
            if ($run->status === 'completed') {
                return true;
            }
            $count++;
        } while ($count < 5);
        return false;
    }
}

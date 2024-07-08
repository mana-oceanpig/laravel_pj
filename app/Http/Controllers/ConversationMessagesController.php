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
        $role_id = 1; // ユーザーのロールID

        // ユーザーのメッセージを保存
        $message = new ConversationMessage();
        $message->conversation_id = $request->input('conversation_id');
        $message->message = $request->input('message');
        $message->role_id = $role_id; // role_id を設定
        $message->save();

        // 会話の最終アクティビティを更新
        $conversation = Conversation::find($request->input('conversation_id'));
        $conversation->last_activity_at = now();
        $conversation->save();
        
        // ChatGPTにメッセージを送信し、レスポンスを取得
        $response = $this->sendMessageToChatGPT($conversation, $message->message);
        
        // ChatGPTのレスポンスを保存
        $responseMessage = new ConversationMessage();
        $responseMessage->conversation_id = $conversation->id;
        $responseMessage->message = $response;
        $responseMessage->role_id = 2; // ChatGPTのロールID
        $responseMessage->save();

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
        $isCompleted = $this->waitUntilRunCompleted($conversation->thread_id, $run->id);
        if (!$isCompleted) {
        throw new \Exception('Failed to complete ChatGPT job');
        }
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
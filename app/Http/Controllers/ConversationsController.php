<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use OpenAI;

class ConversationsController extends Controller
{
    private $client;

    public function __construct()
    {
        $apiKey = env('OPEN_AI_SECRET_KEY');
        if (!$apiKey) {
            throw new \Exception('OpenAI API key is not set');
        }
        $this->client = OpenAI::client($apiKey);
    }

    public function index()
    {
        $user = Auth::user(); // 認証済みユーザーを取得
        $conversations = $user->conversations()->orderBy('created_at', 'desc')->get();
        return view('conversations.index', compact('conversations', 'user'));
    }
    public function show($id)
    {
        try {
            $conversation = Conversation::with(['user', 'messages.user'])->findOrFail($id);
            $messages = $conversation->messages()->orderBy('created_at')->get();
            
            // Load summary message if available
            $summaryMessage = $conversation->messages()->where('summary', true)->first();
    
            return view('conversations.show', compact('conversation', 'messages', 'summaryMessage'));
        } catch (ModelNotFoundException $e) {
            Log::error("Conversation with ID $id not found.");
            abort(404, 'Conversation not found');
        }
    }

    public function listen($id)
    {
        $conversation = Conversation::findOrFail($id);
        if ($conversation->status === Conversation::STATUS_IN_PROGRESS) {
            $conversation->last_activity_at = Carbon::now();
            $conversation->save();
        }
        $messages = $conversation->messages()->latest()->get();
        return view('conversations.listen', compact('conversation', 'messages'));
    }

    public function start()
    {
        return view('conversations.start');
    }
    
    public function updateTitle(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $summaryMessage = $conversation->messages()->where('summary', true)->first();
    
        if ($summaryMessage) {
            $newSummary = preg_replace('/タイトル：.+?(?=\s*要約：|$)/u', 'タイトル：' . $request->input('title'), $summaryMessage->message);
            $summaryMessage->update(['message' => $newSummary]);
        } else {
            // サマリーメッセージがない場合の処理
            // 例: 新しいサマリーメッセージを作成
            $conversation->messages()->create([
                'message' => 'タイトル：' . $request->input('title') . "\n要約：",
                'summary' => true,
                'role_id' => 2, // システムメッセージのrole_id
            ]);
        }
    
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'status' => Conversation::STATUS_IN_PROGRESS,
            'last_activity_at' => Carbon::now(),
        ]);
        return redirect()->route('conversations.listen', $conversation->id);
    }
    public function claimLoginBonus()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $lastBonusDate = $user->last_bonus_date ? Carbon::parse($user->last_bonus_date) : null;

        if (!$lastBonusDate || $lastBonusDate->diffInDays($now) >= 1) {
            // ログインボーナスの付与
            $bonusPoints = 10;
            $user->points += $bonusPoints;
            $user->last_bonus_date = $now;

            // ログインストリークの更新
            if ($lastBonusDate && $lastBonusDate->diffInDays($now) == 1) {
                $user->login_streak++;
            } else {
                $user->login_streak = 1;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => "ログインボーナス{$bonusPoints}ptを獲得しました！",
                'totalPoints' => $user->points,
                'loginStreak' => $user->login_streak
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '本日のログインボーナスは既に獲得済みです。',
            'totalPoints' => $user->points,
            'loginStreak' => $user->login_streak
        ]);
    }
    public function updateLoginStreak()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $lastLoginDate = $user->last_login_at ? Carbon::parse($user->last_login_at) : null;

        if (!$lastLoginDate || $lastLoginDate->diffInDays($now) >= 1) {
            if ($lastLoginDate && $lastLoginDate->diffInDays($now) == 1) {
                $user->login_streak++;
            } else {
                $user->login_streak = 1;
            }

            $user->last_login_at = $now;
            $user->save();

            return response()->json([
                'success' => true,
                'loginStreak' => $user->login_streak
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '今日は既にログイン済みです。',
            'loginStreak' => $user->login_streak
        ]);
    }
    public function getLoginInfo()
    {
        $user = Auth::user();
        return response()->json([
            'totalPoints' => $user->points,
            'loginStreak' => $user->login_streak,
            'lastBonusDate' => $user->last_bonus_date,
            'lastLoginAt' => $user->last_login_at
        ]);
    }

    public function complete(Request $request, $id)
    {
        try {
            $conversation = Conversation::findOrFail($id);
            $user_id = Auth::id();
    
            if ($conversation->user_id !== $user_id) {
                return response()->json([
                    'error' => 'You are not allowed to access this conversation'
                ], 403);
            }
    
            if ($conversation->status === Conversation::STATUS_COMPLETED) {
                return response()->json([
                    'warning' => '対話は既に完了しています。',
                    'conversation_id' => $id
                ]);
            }
    
            // 状態を完了にする
            $conversation->status = Conversation::STATUS_COMPLETED;
            $conversation->save();
    
            // ポイントを加算
            $user = Auth::user();
            $user->points += 10;
            $user->save();
    
            Log::info("User ID {$user->id} has been awarded 10 points for completing conversation ID {$conversation->id}. Total points: {$user->points}");
    
            // サマリーを生成するが、生成に失敗してもポイント加算は維持する
            $summaryGenerated = $this->generateAndSaveSummary($conversation);
    
            if ($summaryGenerated) {
                return response()->json([
                    'message' => '対話が完了し、10ptを獲得しました！サマリーが生成されました。',
                    'conversation_id' => $id,
                    'totalPoints' => $user->points
                ]);
            } else {
                return response()->json([
                    'message' => '対話が完了し、10ptを獲得しましたが、サマリーの生成に失敗しました。',
                    'conversation_id' => $id,
                    'totalPoints' => $user->points
                ]);
            }
        } catch (ModelNotFoundException $e) {
            Log::error("Conversation with ID $id not found.");
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error completing conversation: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while completing the conversation'], 500);
        }
    }

    private function generateAndSaveSummary(Conversation $conversation)
    {
        Log::info('Starting summary generation for conversation: ' . $conversation->id);
        
        $threadId = $conversation->thread_id;

        if (!$threadId) {
            Log::error('No thread ID found for conversation: ' . $conversation->id);
            return false;
        }

        try {
            $message = $this->client->threads()->messages()->create($threadId, [
                'role' => 'user',
                'content' => '今までの会話の内容を要約し、以下の形式で回答してください：\nタイトル：[15文字以内のタイトル]\n要約：[100文字程度の要約]',
            ]);
            Log::info('Summary request sent to thread ID: ' . $threadId . ', Message ID: ' . $message->id);

            $run = $this->client->threads()->runs()->create($threadId, [
                'assistant_id' => Config::get('services.openai.assistant_id'),
            ]);
            Log::info('Run request sent for thread ID: ' . $threadId . ', Run ID: ' . $run->id);

            $isCompleted = $this->waitUntilRunCompleted($threadId, $run->id);
            if (!$isCompleted) {
                Log::error('Failed to generate summary: Run not completed');
                return false;
            }
            Log::info('Summary generation completed for thread ID: ' . $threadId . ', Run ID: ' . $run->id);

            $summaryMessage = $this->getLatestAssistantMessage($threadId);
            Log::info('Retrieved summary message for conversation ID: ' . $conversation->id . ', Message: ' . ($summaryMessage ?? 'No message retrieved'));

            if ($summaryMessage) {
                $message = ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'message' => $summaryMessage,
                    'role_id' => 2,
                    'summary' => true,
                    'is_hidden' => false,
                ]);
                Log::info('Summary message saved for conversation ID: ' . $conversation->id . ', Message ID: ' . $message->id);

                $conversation->touch();
                Log::info('Conversation updated_at set to now for conversation ID: ' . $conversation->id);

                return true;
            } else {
                Log::error('No summary message retrieved for conversation ID: ' . $conversation->id);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error in generateAndSaveSummary: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    private function waitUntilRunCompleted($threadId, $runId)
    {
        $maxAttempts = Config::get('services.openai.max_attempts', 10);
        $attemptDelay = Config::get('services.openai.attempt_delay', 3);

        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep($attemptDelay);
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
            Log::info('Run status check: ' . $run->status . ' for Run ID: ' . $runId);
            if ($run->status === 'completed') {
                return true;
            } elseif ($run->status === 'failed') {
                Log::error('Run failed for thread ID: ' . $threadId . ', Run ID: ' . $runId);
                return false;
            }
        }

        Log::error('Timeout waiting for run completion. Thread ID: ' . $threadId . ', Run ID: ' . $runId);
        return false;
    }

    private function getLatestAssistantMessage($threadId)
    {
        try {
            $messages = $this->client->threads()->messages()->list($threadId, [
                'order' => 'desc',
                'limit' => 1,
            ]);

            if (is_object($messages) && property_exists($messages, 'data') && is_array($messages->data)) {
                foreach ($messages->data as $message) {
                    if (is_object($message) && $message->role === 'assistant' && is_array($message->content)) {
                        foreach ($message->content as $content) {
                            if (is_object($content) && $content->type === 'text') {
                                return $content->text->value;
                            }
                        }
                    }
                }
            }

            Log::error('No valid assistant message found in the response');
            return null;
        } catch (\Exception $e) {
            Log::error('Error in getLatestAssistantMessage: ' . $e->getMessage());
            return null;
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $conversation = Conversation::findOrFail($id);
            $conversation->markAsCanceled();
            return response()->json([
                'message' => '対話がキャンセルされました。',
                'conversation_id' => $id
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error("Conversation with ID $id not found.");
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error canceling conversation: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while canceling the conversation'], 500);
        }
    }

    public function updateLastActivity(Request $request, $id)
    {
        try {
            $conversation = Conversation::findOrFail($id);
            $conversation->last_activity_at = $request->input('last_activity_at');
            $conversation->save();
            return response()->json(['status' => 'success']);
        } catch (ModelNotFoundException $e) {
            Log::error("Conversation with ID $id not found.");
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error updating last activity: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while updating last activity'], 500);
        }
    }

    public function destroy(Conversation $conversation)
    {
        try {
            $conversation->delete();
            Log::info('Conversation deleted: ' . $conversation->id);
            return redirect()->route('conversations.index')->with('success', '対話が削除されました。');
        } catch (\Exception $e) {
            Log::error('Error deleting conversation: ' . $e->getMessage());
            return redirect()->route('conversations.index')->with('error', '対話の削除中にエラーが発生しました。');
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use OpenAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class ConversationsController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPEN_AI_SECRET_KEY'));
    }

    public function index()
    {
        $conversations = Conversation::all();
        return view('conversations.index', compact('conversations'));
    }

    public function show($id)
    {
        try {
            $conversation = Conversation::with('user', 'messages.user')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("Conversation with ID $id not found.");
            abort(404, 'Conversation not found');
        }

        $messages = $conversation->messages()->orderBy('created_at')->get();
        return view('conversations.show', compact('conversation', 'messages'));
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

    public function store(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'status' => Conversation::STATUS_IN_PROGRESS,
            'last_activity_at' => Carbon::now(),
        ]);
        return redirect()->route('conversations.listen', $conversation->id);
    }
    public function complete(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);

        // If conversation is already completed
        if ($conversation->status === Conversation::STATUS_COMPLETED) {
            return redirect()->route('conversations.show', $id)->with('warning', '対話は既に完了しています。');
        }

        // Complete conversation
        $conversation->status = Conversation::STATUS_COMPLETED;
        $conversation->last_activity_at = Carbon::now();
        $conversation->save();

        return redirect()->route('conversations.show', $id)->with('success', '対話が完了しました。');
    }

    public function cancel(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->markAsCanceled();
        return redirect()->route('conversations.show', $id)->with('success', '対話がキャンセルされました。');
    }

    public function updateLastActivity(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->last_activity_at = $request->input('last_activity_at');
        $conversation->save();
        return response()->json(['status' => 'success']);
    }
    public function destroy(Conversation $conversation)
    {
    $conversation->delete();
    return redirect()->route('conversations.index')->with('success', '対話が削除されました。');
    }

}
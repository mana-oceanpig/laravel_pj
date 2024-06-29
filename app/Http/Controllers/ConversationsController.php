<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 

class ConversationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conversation= Conversation::where('user_id', Auth::id())
            ->with('lastMessage')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('conversations', [
            'conversations' => $conversation
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $conversation = Conversation::create([
            'users_id' => Auth::id(),
            'external_id' => uniqid('conv_', true),
        ]);

        return view('conversation.create');
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'initial_message' => 'required|string',
        ]);
        
        $conversation = Conversation::create([
        'users_id' => Auth::id(),
        'external_id' => uniqid('conv_', true),
        ]);

        return redirect('/conversations');
  
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation)
    {
        return view('conversation.show', compact('conversation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conversation $conversation)
    {
        return view('conversation.edit', compact('conversation'));
    }

    /**
     * Update the specified resource in storage.
     */
    //アップデートファンクションは必要なのか？新しい会話を始めるだけで良いのでは？

    // public function update(Request $request, Conversation $conversation) 
    // {
    //     $conversation = Conversation::where('user_id', Auth::id())->findOrFail($id);
    //     $conversation->update($request->only(''));
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversation)
    {
        $conversation->delete();      
        return redirect('/');  
    }
}
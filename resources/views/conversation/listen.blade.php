@extends('layouts.app')

@section('content')
    <h1>今日の対話 - ID: {{ $conversation->id }}</h1>
    
    <div id="messages-container" style="max-height: 400px; overflow-y: auto;">
        @foreach($messages->reverse() as $message)
            <div class="message" style="text-align: {{ $message->role_id == 1 ? 'left' : 'right' }};">
                <div><strong>{{ $message->role_id == 1 ? $conversation->user->name : 'カウンセラー' }}</strong></div>
                <div>{{ $message->message }}</div>
                @if ($message->role_id == 1)
                    <small class="text-muted">{{ $message->created_at->format('Y-m-d H:i:s') }}</small>
                @endif
            </div>
            <hr> <!-- Optional: Add a line separator between messages -->
        @endforeach
    </div>

    <form id="message-form" action="{{ route('conversationMessages.store', ['conversation' => $conversation->id]) }}" method="POST">
        @csrf
        <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
        <input type="text" name="message" id="message-input" required>
        <button type="submit">送信</button>
    </form>
    <br>

    <form action="{{ route('conversations.complete', $conversation->id) }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-success">対話を終了</button>
    </form>

    <form action="{{ route('conversations.cancel', $conversation->id) }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-warning">対話をキャンセル</button>
    </form>

    <a href="{{ route('conversations.index') }}" class="btn btn-secondary">対話一覧に戻る</a>
@endsection

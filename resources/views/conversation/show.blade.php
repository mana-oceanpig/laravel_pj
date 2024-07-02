@extends('layouts.app')

@section('content')
    <h1>会話詳細 - ID: {{ $conversation->id }}</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">ステータス: {{ $conversation->status }}</h5>
            <p class="card-text">最終アクティビティ: {{ optional($conversation->last_activity_at)->format('Y-m-d H:i:s') }}</p>
            <p class="card-text">作成日時: {{ optional($conversation->created_at)->format('Y-m-d H:i:s') }}</p>
            <p class="card-text">更新日時: {{ optional($conversation->updated_at)->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    <h2 class="mt-4">メッセージ一覧</h2>
    <div id="messages-container">
        @foreach($messages as $message)
            <div class="message">
                <strong>{{ $message->sender }}:</strong> {{ $message->content }}
                <small class="text-muted">{{ optional($message->created_at)->format('Y-m-d H:i:s') }}</small>
            </div>
        @endforeach
    </div>

    @if($conversation->status === 'inProgress')
        <a href="{{ route('conversations.listen', $conversation->id) }}" class="btn btn-primary mt-3">会話を続ける</a>
    @endif

    <a href="{{ route('conversations.index') }}" class="btn btn-secondary mt-3">会話一覧に戻る</a>
@endsection

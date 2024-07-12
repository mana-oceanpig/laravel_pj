@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-blue: #3498db;
        --primary-green: #2ecc71;
        --primary-orange: #f39c12;
        --light-bg: #ecf0f1;
    }
    body {
        background-color: var(--light-bg);
    }
    .logo {
        max-width: 150px;
        margin-bottom: 1rem;
    }
    .gradient-button {
        background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
        border: none;
        color: white;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .gradient-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(50, 50, 93, .1), 0 2px 4px rgba(0, 0, 0, .08);
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
        margin-bottom: 0.75rem;
    }
    
    .card-text {
        font-size: 1rem;
        line-height: 1.5;
    }
    }
    h1, h2 {
        color: var(--primary-blue);
    }
    #messages-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 1rem;
        background-color: white;
        border-radius: 15px;
    }
    .message {
        margin-bottom: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        max-width: 80%;
    }
    .message-user {
        background-color: var(--primary-blue);
        color: white;
        align-self: flex-end;
    }
    .message-counselor {
        max-width: 50%;
        background-color: #f1f1f1;
        color: #333;
        align-self: flex-start;
    }
    .badge {
        padding: 0.5em 1em;
        border-radius: 20px;
        font-weight: normal;
    }
    .summary-container {
        margin: 0 auto;
    }
    
    .summary-text {
        font-size: 1rem;
        line-height: 1.6;
        color: #555;
    }
    
    @media (min-width: 768px) {
        .summary-container {
            max-width: 70%;
            align-self: flex-start;
        }
        .card-text {
            font-size: 0.875rem;
        }
    }
    
    @media (min-width: 992px) {
        .summary-container {
            max-width: 60%;
        }
    }
</style>

<div class="container py-4">
    @if ($summaryMessage)
        @php
            $summary = $summaryMessage->message;
            $titleMatch = preg_match('/タイトル：(.+?)(?=\s*要約：|$)/u', $summary, $titleMatches);
            $summaryMatch = preg_match('/要約：(.+)$/u', $summary, $summaryMatches);
            
            $title = $titleMatch ? trim($titleMatches[1]) : '';
            $summaryText = $summaryMatch ? trim($summaryMatches[1]) : '';
        @endphp
        
        <div class="text-center mb-4">
            <h1 class="mb-3">{{ $title ?: 'タイトルなし' }}</h1>
        </div>
    @else
        <div class="text-center mb-4">
            <h1 class="mb-3">タイトル：{{ $conversation->title }}</h1>
        </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between">
            <div class="card-details">
                <h5 class="card-title">ステータス: 
                    @php
                        $statusClass = '';
                        $statusText = '';
                        switch($conversation->status) {
                            case 'inProgress':
                                $statusClass = 'bg-primary';
                                $statusText = '進行中';
                                break;
                            case 'completed':
                                $statusClass = 'bg-success';
                                $statusText = '完了';
                                break;
                            case 'cancelled':
                                $statusClass = 'bg-warning';
                                $statusText = 'キャンセル';
                                break;
                            default:
                                $statusClass = 'bg-secondary';
                                $statusText = $conversation->status;
                        }
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                </h5>
                <p class="card-text">最終アクティビティ: {{ optional($conversation->last_activity_at)->format('Y年m月d日 H:i:s') }}</p>
                <p class="card-text">作成日時: {{ optional($conversation->created_at)->format('Y年m月d日 H:i:s') }}</p>
                <p class="card-text">更新日時: {{ optional($conversation->updated_at)->format('Y年m月d日 H:i:s') }}</p>
            </div>
            @if ($summaryMessage && $summaryText)
                <div class="mt-3 summary-container">
                    <h5 class="card-title">今回のサマリ：</h5>
                    <p class="card-text summary-text">{{ $summaryText }}</p>
                </div>
            @endif
        </div>
    </div>

    <h2 class="mt-4 mb-3">メッセージ一覧</h2>
    <div id="messages-container" class="card">
        <div class="card-body d-flex flex-column">
            @foreach($messages as $message)
                @if(!$message->summary)
                    <div class="message {{ $message->role_id == 1 ? 'message-user' : 'message-counselor' }}">
                        <div><strong>{{ $message->role_id == 1 ? $conversation->user->name : 'カウンセラー' }}</strong></div>
                        <div>{{ $message->message }}</div>
                        <div><small class="text-muted">{{ optional($message->created_at)->format('Y年m月d日 H:i:s') }}</small></div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        @if($conversation->status === 'inProgress')
            <a href="{{ route('conversations.listen', $conversation->id) }}" class="gradient-button btn rounded-pill px-4 py-2">会話を続ける</a>
        @endif
        <a href="{{ route('conversations.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">会話一覧に戻る</a>
    </div>
</div>
@endsection
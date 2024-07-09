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
        max-width: 200px;
        margin-bottom: 2rem;
    }
    .gradient-button {
        background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
        border: none;
        color: white;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .gradient-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, .1), 0 3px 6px rgba(0, 0, 0, .08);
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: none;
        position: relative;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .badge {
        border-radius: 50px;
        padding: 8px 15px;
    }
    .delete-button {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        color: var(--primary-orange);
        font-size: 1.5rem;
        cursor: pointer;
    }
    .delete-button:hover {
        color: #e74c3c;
    }
    h1, h2 {
        color: var(--primary-blue);
    }
    
</style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="mb-4">ようこそ！</h1>
    </div>
    
    <div class="d-flex justify-content-center mb-5">
        <a href="{{ route('conversations.start') }}" class="gradient-button btn btn-lg rounded-circle" style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            話しかける
        </a>
    </div>
    
    <h2 class="text-center mb-4">これまでの対話</h2>
    
    <div class="row">
        @foreach($conversations as $conversation)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="card-title" style="color: var(--primary-green);">対話 #{{ $conversation->id }}</h5>
                        <p class="card-text">
                            <i class="fas fa-clock mr-2" style="color: var(--primary-orange);"></i>
                            {{ \Carbon\Carbon::parse($conversation->last_activity_at)->format('Y年m月d日 H:i') }}
                        </p>
                        <p class="card-text">
                            @if($conversation->status === App\Models\Conversation::STATUS_IN_PROGRESS)
                                <span class="badge bg-primary" style="background-color: var(--primary-blue) !important;">進行中</span>
                            @elseif($conversation->status === App\Models\Conversation::STATUS_COMPLETED)
                                <span class="badge bg-success" style="background-color: var(--primary-green) !important;">完了</span>
                            @elseif($conversation->status === App\Models\Conversation::STATUS_CANCELED)
                                <span class="badge bg-cancel" style="background-color: var(--primary-orange) !important;">キャンセル</span>
                            @else
                                <span class="badge bg-secondary">{{ $conversation->status }}</span>
                            @endif
                        </p>
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('conversations.show', $conversation->id) }}" class="btn btn-outline-primary rounded-pill" style="color: var(--primary-blue); border-color: var(--primary-blue);">詳細を見る</a>
                            @if($conversation->status === App\Models\Conversation::STATUS_IN_PROGRESS)
                                <a href="{{ route('conversations.listen', $conversation->id) }}" class="gradient-button btn rounded-pill">対話を続ける</a>
                            @endif
                            <!-- 削除ボタン -->
                            <button type="button" class="delete-button" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $conversation->id }}">✖️</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 削除確認モーダル -->
            <div class="modal fade" id="deleteModal{{ $conversation->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $conversation->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel{{ $conversation->id }}">削除確認</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            本当にこの対話を削除しますか？
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                            <form action="{{ route('conversations.destroy', $conversation->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">削除</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

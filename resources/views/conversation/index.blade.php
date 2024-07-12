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
    .card-body {
        padding: 1.25rem;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .badge {
        border-radius: 50px;
        padding: 8px 15px;
    }
    .card-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .edit-button,
    .delete-button {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        transition: transform 0.3s ease;
        padding: 0;
    }

    .edit-button:hover,
    .delete-button:hover {
        transform: scale(1.2);
    }
    .delete-button {
        position: absolute;
        top: 1rem;
        right: 1rem;
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
                        <button type="button" class="delete-button" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $conversation->id }}" aria-label="削除">
                            ×️
                        </button>
                        <h5 class="card-title" style="color: var(--primary-green);">
                            <span id="title-{{ $conversation->id }}">
                                @php
                                    $summaryMessage = $conversation->messages()->where('summary', true)->first();
                                    $title = '';
                                    if ($summaryMessage) {
                                        preg_match('/タイトル：(.+?)(?=\s*要約：|$)/u', $summaryMessage->message, $matches);
                                        $title = $matches[1] ?? '';
                                    }
                                    echo $title ?: '対話 #' . $conversation->id;
                                @endphp
                            </span>
                            <button class="edit-button" data-bs-toggle="modal" data-bs-target="#editTitleModal{{ $conversation->id }}" aria-label="編集">
                                ️🖊️
                            </button>
                        </h5>
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
                        </div>
                    </div>
                </div>
                
                <!-- 編集モーダル -->
                <div class="modal fade" id="editTitleModal{{ $conversation->id }}" tabindex="-1" aria-labelledby="editTitleModalLabel{{ $conversation->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTitleModalLabel{{ $conversation->id }}">タイトルの編集</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-form-{{ $conversation->id }}" onsubmit="updateTitle(event, {{ $conversation->id }})">
                                    <div class="mb-3">
                                        <label for="new-title-{{ $conversation->id }}" class="form-label">新しいタイトル</label>
                                        <input type="text" class="form-control" id="new-title-{{ $conversation->id }}" value="{{ $title ?: '対話 #' . $conversation->id }}">
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                        <button type="submit" class="btn btn-primary">保存</button>
                                    </div>
                                </form>
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
                                <p>本当にこの対話を削除しますか？</p>
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
            </div>
        @endforeach
    </div>
</div>
<script>
function editTitle(id) {
    document.getElementById('title-' + id).style.display = 'none';
    document.getElementById('edit-form-' + id).style.display = 'block';
}

function updateTitle(event, id) {
    event.preventDefault();
    var newTitle = document.getElementById('new-title-' + id).value;
    
    fetch('{{ route("conversations.updateTitle", ":id") }}'.replace(':id', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ title: newTitle })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP status ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('title-' + id).textContent = newTitle;
            var modal = bootstrap.Modal.getInstance(document.getElementById('editTitleModal' + id));
            modal.hide();
        } else {
            alert('タイトルの更新に失敗しました。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました: ' + error.message);
    });
}
</script>
@endsection

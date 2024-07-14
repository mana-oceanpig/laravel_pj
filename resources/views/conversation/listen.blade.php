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
        border-radius: 25px;
        padding: 0.5rem 1rem;
        cursor: pointer;
    }

    .gradient-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(50, 50, 93, .1), 0 2px 4px rgba(0, 0, 0, .08);
    }

    .input-group {
        display: flex;
        align-items: center;
        background-color: white;
        border-radius: 25px;
        padding: 0.5rem;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .input-group-append {
        display: flex;
        align-items: center;
        margin-left: 0.5rem;
    }

    .input-group-append .btn {
        margin-left: 0.5rem;
    }

    #voice-button,
    .btn-primary {
        color: var(--primary-blue);
        padding: 1rem;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #voice-button:hover,
    .btn-primary:hover {
        background-color: rgba(52, 152, 219, 0.1);
    }

    #voice-button.active {
        background-color: var(--primary-blue);
        color: white;
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border: none;
    }
    h1 {
        color: var(--primary-blue);
    }
    #messages-container {
        max-height: 400px;
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
    #message-input {
        border: none;
        outline: none;
        flex: 1;
    }
    #thinking-message {
        max-width: 30%;
        background-color: #f1f1f1;
        color: #333;
        align-self: flex-start;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
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
    .btn-end {
        background: linear-gradient(45deg, var(--primary-green), #27ae60);
        border: none;
        color: white;
    }
    .btn-cancel {
        background: linear-gradient(45deg, var(--primary-orange), #e67e22);
        border: none;
        color: white;
    }
</style>

<div class="container py-4">
    <div class="text-center mb-4">
        <h1 class="mb-3">今日の対話 - {{ now()->format('m月d日') }}</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div id="messages-container" class="d-flex flex-column">
                @foreach($messages->reverse() as $message)
                    <div class="message {{ $message->role_id == 1 ? 'message-user' : 'message-counselor' }}">
                        <div><strong>{{ $message->role_id == 1 ? $conversation->user->name : 'カウンセラー' }}</strong></div>
                        <div>{{ $message->message }}</div>
                        <small class="text-muted">{{ $message->created_at->format('Y-m-d H:i:s') }}</small>
                    </div>
                @endforeach
            </div>
            <div id="thinking-message" class="message message-counselor" style="display: none;">
                <div><strong>カウンセラー</strong></div>
                <div>...考え中</div>
            </div>
        </div>
    </div>

    <form id="message-form" class="mb-4">
        @csrf
        <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
        <div class="input-group">
            <input type="text" name="message" id="message-input" class="form-control rounded-pill" required placeholder="メッセージを入力...">
            <div class="input-group-append">
                <button type="button" id="voice-button" class="btn btn-outline-primary rounded-pill">
                    <i class="bi bi-mic">️️</i>🎙
                </button>
                <button type="submit" class="btn btn-primary rounded-pill">
                    <i class="bi bi-send"></i>📤️
                </button>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between">
        <button id="end-conversation" class="btn btn-end rounded-pill px-4 py-2">対話を終了</button>
        <button id="cancel-conversation" class="btn btn-cancel rounded-pill px-4 py-2">対話をキャンセル</button>
        <a href="{{ route('conversations.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">対話一覧に戻る</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    const thinkingMessage = document.getElementById('thinking-message');
    const summaryContent = document.getElementById('summary-content');
    const endConversationButton = document.getElementById('end-conversation');
    const cancelConversationButton = document.getElementById('cancel-conversation');
    const voiceButton = document.getElementById('voice-button');

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'message-user' : 'message-counselor'}`;
        messageDiv.innerHTML = `
            <div><strong>${isUser ? '{{ $conversation->user->name }}' : 'カウンセラー'}</strong></div>
            <div>${message}</div>
            <small class="text-muted">${new Date().toLocaleString()}</small>
        `;
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }

    //音声入力
    let recognition = null;

    voiceButton.addEventListener('click', function() {
        if (recognition && recognition.running) {
            recognition.stop();
            return;
        }

        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();

            recognition.onstart = function() {
                voiceButton.classList.add('active');
            };

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                messageInput.value = transcript;
            };

            recognition.onend = function() {
                voiceButton.classList.remove('active');
            };

            recognition.onerror = function(event) {
                console.error('音声認識中にエラーが発生しました:', event.error);
                voiceButton.classList.remove('active');
            };

            recognition.start();
        } else {
            alert('ご使用のブラウザは音声入力をサポートしていません。');
        }
    });

    messageForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        addMessage(message, true);
        messageInput.value = '';
        thinkingMessage.style.display = 'block';
        scrollToBottom();

        fetch('{{ route('conversationMessages.store', ['conversation' => $conversation->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                conversation_id: {{ $conversation->id }},
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            thinkingMessage.style.display = 'none';
            if (data.summary) {
                summaryContent.textContent = data.summary;
            } else {
                addMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            thinkingMessage.style.display = 'none';
            addMessage('エラーが発生しました。もう一度お試しください。');
        });
    });

    endConversationButton.addEventListener('click', function() {
        fetch('{{ route('conversations.complete', $conversation->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            window.location.href = '{{ route('conversations.show', $conversation->id) }}';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('対話の終了中にエラーが発生しました。');
        });
    });

    cancelConversationButton.addEventListener('click', function() {
        fetch('{{ route('conversations.cancel', $conversation->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            window.location.href = '{{ route('conversations.show', $conversation->id) }}';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('対話のキャンセル中にエラーが発生しました。');
        });
    });

    scrollToBottom();
});
</script>
@endsection


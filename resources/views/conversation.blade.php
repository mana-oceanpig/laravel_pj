<!-- resources/views/conversation/index.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', __('LuminaMind - AIカウンセリングで心の健康をサポート'))">
    <title>LuminaMind</title>
    <style></style>
</head>
<body>
<div class="conversation-container">
    <h2>AIカウンセラーとの会話</h2>
    <div id="conversation-history">
        @foreach($conversations as $conversation)
            <p><strong>あなた:</strong> {{ $conversation->message }}</p>
            <p><strong>AI:</strong> {{ $conversation->ai_response }}</p>
        @endforeach
    </div>
    <form id="conversation-form">
        @csrf
        <div class="form-group">
            <label for="message">メッセージ:</label>
            <textarea id="message" name="message" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">送信</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('conversation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const message = document.getElementById('message').value;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route("conversation.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        const conversationHistory = document.getElementById('conversation-history');
        conversationHistory.innerHTML += `<p><strong>あなた:</strong> ${message}</p>`;
        conversationHistory.innerHTML += `<p><strong>AI:</strong> ${data.ai_response}</p>`;
        document.getElementById('message').value = '';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('メッセージの送信中にエラーが発生しました。もう一度お試しください。');
    });
});
</script>
@endsection
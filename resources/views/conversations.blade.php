<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIカウンセラーと会話を始める</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50E3C2;
            --accent-color: #F5A623;
            --text-color: #333333;
            --background-color: #FFFFFF;
        }
        </style>
</head>

<body>
    <div class="conversation-container">
    <h2>AIカウンセラーとの会話</h2>
    <ul>

    </ul>
    
    <form id="conversation-form" class="flex" method="POST" action="{{ route('conversations.store') }}" >
        @csrf
        <div class="form-group">
            <lavel for="title">会話のタイトル：</lavel>
            <input id="title" name="title" type="text" required>
        </div>
        <div class="form-group">
            <label for="initial_message">メッセージ:</label>
            <textarea id="initial_message" name="initial_message" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">送信</button>
    </form>
</div>
</body>
</html>
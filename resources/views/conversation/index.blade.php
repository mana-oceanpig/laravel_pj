@extends('layouts.app')

@section('content')
    <h1>会話一覧</h1>
    
    <a href="{{ route('conversations.start') }}" class="btn btn-primary">新しい会話を開始</a>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>ステータス</th>
                <th>最終アクティビティ</th>
                <th>アクション</th>
            </tr>
        </thead>
        <tbody>
            @foreach($conversations as $conversation)
                <tr>
                    <td>{{ $conversation->id }}</td>
                    <td>{{ $conversation->status }}</td>
                    <td>{{ \Carbon\Carbon::parse($conversation->last_activity_at)->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <a href="{{ route('conversations.show', $conversation->id) }}" class="btn btn-sm btn-info">詳細</a>
                        @if($conversation->status === App\Models\Conversation::STATUS_IN_PROGRESS)
                            <a href="{{ route('conversations.listen', $conversation->id) }}" class="btn btn-sm btn-primary">リッスン</a>
                            <form action="{{ route('conversations.complete', $conversation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success">完了</button>
                            </form>
                            <form action="{{ route('conversations.cancel', $conversation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning">キャンセル</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
@extends('layouts.app')

@section('content')
    <h1>ようこそ</h1>
    <h2>対話一覧</h2>
    
    <a href="{{ route('conversations.start') }}" class="btn btn-primary">新しい対話を開始</a>

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
                            <a href="{{ route('conversations.listen', $conversation->id) }}" class="btn btn-sm btn-primary">対話を再開</a>
                            <form action="{{ route('conversations.complete', $conversation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success">対話を完了</button>
                            </form>
                            <form action="{{ route('conversations.cancel', $conversation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning">対話をキャンセル</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
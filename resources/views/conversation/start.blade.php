@extends('layouts.app')

@section('content')
    <h1>新しい会話を開始</h1>

    <div class="card mb-4">
        <div class="card-body">
            <p>新しい会話を開始しますか？</p>
            <form action="{{ route('conversations.store') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">新しい会話を開始</button>
            </form>
            <a href="{{ route('conversations.index') }}" class="btn btn-secondary mt-3">会話一覧に戻る</a>
        </div>
    </div>
@endsection

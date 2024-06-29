@extends('layouts.app')

@section('content')
    <h1>{{ $conversations->name }}</h1>

    <ul>
        @foreach($conversations->messages as $message)
            <li>{{ $conversations->user->name }}: {{ $message->content }}</li>
        @endforeach
    </ul>

    <form action="{{ route('conversations.store') }}" method="POST">
        @csrf
        <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
        <input type="text" name="content" placeholder="メッセージ">
        <button type="submit">送信</button>
    </form>
@endsection

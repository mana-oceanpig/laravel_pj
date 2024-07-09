<section>
    <header>
        <h2 class="text-lg font-medium text-primary">
            プロフィール情報
        </h2>

        <p class="mt-1 text-sm text-muted">
            アカウントのプロフィール情報とメールアドレスを更新します。
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">名前</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-muted">
                        メールアドレスが未確認です。
                        <a href="{{ route('verification.send') }}" class="text-primary">ここをクリックして確認メールを再送信してください。</a>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-success">
                            新しい確認リンクがメールアドレスに送信されました。
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center">
            <button type="submit" class="btn btn-primary">保存</button>

            @if (session('status') === 'profile-updated')
                <p class="text-success ms-3">保存しました。</p>
            @endif
        </div>
    </form>
</section>
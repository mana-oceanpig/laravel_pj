<section>
    <header>
        <h2 class="text-lg font-medium text-primary">
            パスワードの更新
        </h2>

        <p class="mt-1 text-sm text-muted">
            アカウントのセキュリティを確保するために、長くてランダムなパスワードを使用してください。
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="current_password" class="form-label">現在のパスワード</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">新しいパスワード</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">新しいパスワード（確認）</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        <div class="d-flex align-items-center">
            <button type="submit" class="btn btn-primary">保存</button>

            @if (session('status') === 'password-updated')
                <p class="text-success ms-3">保存しました。</p>
            @endif
        </div>
    </form>
</section>
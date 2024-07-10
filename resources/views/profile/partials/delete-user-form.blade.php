<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-danger">
            アカウントの削除
        </h2>

        <p class="mt-1 text-sm text-muted">
            アカウントを削除すると、すべてのリソースとデータが完全に削除されます。アカウントを削除する前に、保持したいデータや情報をダウンロードしてください。
        </p>
    </header>

    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
        アカウントを削除
    </button>

    <div class="modal fade" id="confirm-user-deletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionLabel">アカウントを削除しますか？</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">
                            アカウントを削除すると、すべてのリソースとデータが完全に削除されます。アカウントを永久に削除することを確認するために、パスワードを入力してください。
                        </p>
                        <div class="mb-3">
                            <label for="password" class="form-label">パスワード</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-danger">アカウントを削除</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
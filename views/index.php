<div class="row g-4">
    <section class="col-lg-8" aria-label="TODO管理">
        <nav class="nav nav-pills flex-wrap gap-2 mb-3" aria-label="カテゴリで絞り込む">
            <a class="nav-link <?= $selectedCategory === null && !$showUncategorized ? 'active' : '' ?>" href="index.php">すべて</a>
            <?php foreach ($categories as $category): ?>
                <a class="nav-link <?= $selectedCategory === (int) $category['id'] ? 'active' : '' ?>" href="index.php?category=<?= (int) $category['id'] ?>">
                    <?= e((string) $category['name']) ?>
                </a>
            <?php endforeach; ?>
            <a class="nav-link <?= $showUncategorized ? 'active' : '' ?>" href="index.php?category=none">未分類</a>
        </nav>

        <form class="card card-body mb-3" action="index.php" method="post">
            <?= csrfInput() ?>
            <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
            <input type="hidden" name="action" value="<?= $editingTodo === null ? 'add_todo' : 'update_todo' ?>">
            <?php if ($editingTodo !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $editingTodo['id'] ?>">
            <?php endif; ?>
            <div class="row g-2 align-items-center">
                <div class="col-md">
                    <label class="visually-hidden" for="title">TODO</label>
                    <input class="form-control" id="title" name="title" type="text" maxlength="120" placeholder="新しいTODOを入力" value="<?= $editingTodo === null ? '' : e((string) $editingTodo['title']) ?>" required autofocus>
                </div>
                <div class="col-md-auto">
                    <label class="visually-hidden" for="category_id">カテゴリ</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">カテゴリなし</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= $editingTodo !== null && (int) $editingTodo['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                <?= e((string) $category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><?= $editingTodo === null ? '追加' : '保存' ?></button>
                    <?php if ($editingTodo !== null): ?>
                        <a class="btn btn-outline-secondary" href="index.php<?= $returnCategory === '' ? '' : '?category=' . rawurlencode($returnCategory) ?>">キャンセル</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if (is_string($message) && $message !== ''): ?>
            <div class="alert alert-success" role="status"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($todos === []): ?>
            <div class="card card-body text-center text-secondary py-5">
                <p class="fw-bold mb-1">表示するTODOはありません。</p>
                <span>新しいタスクを追加してみましょう。</span>
            </div>
        <?php else: ?>
            <ul class="list-group shadow-sm">
                <?php foreach ($todos as $todo): ?>
                    <li class="list-group-item d-flex align-items-center gap-3 <?= (int) $todo['is_completed'] === 1 ? 'todo-completed' : '' ?>">
                        <form action="index.php" method="post">
                            <?= csrfInput() ?>
                            <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                            <input type="hidden" name="action" value="toggle_todo">
                            <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                            <button class="btn btn-sm <?= (int) $todo['is_completed'] === 1 ? 'btn-success' : 'btn-outline-secondary' ?> rounded-circle check-button" type="submit" aria-label="<?= (int) $todo['is_completed'] === 1 ? '未完了に戻す' : '完了にする' ?>">
                                <?= (int) $todo['is_completed'] === 1 ? '✓' : '' ?>
                            </button>
                        </form>
                        <span class="flex-grow-1 text-break">
                            <?= e((string) $todo['title']) ?>
                            <?php if ($todo['category_name'] !== null): ?>
                                <small class="badge text-bg-primary ms-2"><?= e((string) $todo['category_name']) ?></small>
                            <?php endif; ?>
                        </span>
                        <a class="btn btn-sm btn-outline-primary" href="index.php?edit=<?= (int) $todo['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                        <form action="index.php" method="post">
                            <?= csrfInput() ?>
                            <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                            <input type="hidden" name="action" value="delete_todo">
                            <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">削除</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <aside class="col-lg-4" aria-label="カテゴリ管理">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title">カテゴリ</h2>
                <form action="index.php" method="post">
                    <?= csrfInput() ?>
                    <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                    <input type="hidden" name="action" value="<?= $editingCategory === null ? 'add_category' : 'update_category' ?>">
                    <?php if ($editingCategory !== null): ?>
                        <input type="hidden" name="id" value="<?= (int) $editingCategory['id'] ?>">
                    <?php endif; ?>
                    <label class="visually-hidden" for="category_name">カテゴリ名</label>
                    <div class="input-group mb-3">
                        <input class="form-control" id="category_name" name="name" type="text" maxlength="40" placeholder="新しいカテゴリ" value="<?= $editingCategory === null ? '' : e((string) $editingCategory['name']) ?>" required>
                        <button class="btn btn-primary" type="submit"><?= $editingCategory === null ? '作成' : '保存' ?></button>
                    </div>
                    <?php if ($editingCategory !== null): ?>
                        <a class="btn btn-sm btn-outline-secondary mb-3" href="index.php">キャンセル</a>
                    <?php endif; ?>
                </form>
                <ul class="list-group list-group-flush">
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item px-0 d-flex align-items-center gap-2">
                            <span class="flex-grow-1 text-break"><?= e((string) $category['name']) ?></span>
                            <a class="btn btn-sm btn-outline-primary" href="index.php?edit_category=<?= (int) $category['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                            <form action="index.php" method="post">
                                <?= csrfInput() ?>
                                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">削除</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>
<?php renderFooter(); ?>

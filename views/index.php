<section class="summary-grid mb-4" aria-label="TODOの状況">
    <article class="summary-card summary-card-primary">
        <span>すべてのTODO</span>
        <strong><?= $todoSummary['total'] ?></strong>
        <small>登録済みのタスク</small>
    </article>
    <article class="summary-card">
        <span>未完了</span>
        <strong><?= $todoSummary['remaining'] ?></strong>
        <small>次に取り組むタスク</small>
    </article>
    <article class="summary-card">
        <span>完了</span>
        <strong><?= $todoSummary['completed'] ?></strong>
        <small>片付いたタスク</small>
    </article>
</section>

<div class="row g-4 g-xl-5">
    <section class="col-lg-8" aria-label="TODO管理">
        <div class="content-card p-3 p-md-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <p class="section-kicker mb-1">TASK COMPOSER</p>
                    <h2 class="h4 mb-0"><?= $editingTodo === null ? '新しいタスクを追加' : 'タスクを編集' ?></h2>
                </div>
                <?php if ($editingTodo !== null): ?>
                    <a class="btn btn-sm btn-light" href="index.php<?= $returnCategory === '' ? '' : '?category=' . rawurlencode($returnCategory) ?>">編集をやめる</a>
                <?php endif; ?>
            </div>
            <form action="index.php" method="post">
                <?= csrfInput() ?>
                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                <input type="hidden" name="action" value="<?= $editingTodo === null ? 'add_todo' : 'update_todo' ?>">
                <?php if ($editingTodo !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingTodo['id'] ?>">
                <?php endif; ?>
                <div class="row g-2">
                    <div class="col-md-7">
                        <label class="form-label visually-hidden" for="title">TODO</label>
                        <input class="form-control form-control-lg" id="title" name="title" type="text" maxlength="120" placeholder="例：企画書の下書きを作成" value="<?= $editingTodo === null ? '' : e((string) $editingTodo['title']) ?>" required autofocus>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label visually-hidden" for="category_id">カテゴリ</label>
                        <select class="form-select form-select-lg" id="category_id" name="category_id">
                            <option value="">カテゴリなし</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $editingTodo !== null && (int) $editingTodo['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                    <?= e((string) $category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary btn-lg" type="submit"><?= $editingTodo === null ? '追加' : '保存' ?></button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (is_string($message) && $message !== ''): ?>
            <div class="alert alert-success shadow-sm" role="status">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <div class="content-card p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <p class="section-kicker mb-1">YOUR TASKS</p>
                    <h2 class="h4 mb-0">タスク一覧</h2>
                </div>
                <span class="task-count"><?= count($todos) ?> 件</span>
            </div>
            <nav class="filter-nav mb-4" aria-label="カテゴリで絞り込む">
                <a class="<?= $selectedCategory === null && !$showUncategorized ? 'is-active' : '' ?>" href="index.php" <?= $selectedCategory === null && !$showUncategorized ? 'aria-current="page"' : '' ?>>すべて</a>
                <?php foreach ($categories as $category): ?>
                    <a class="<?= $selectedCategory === (int) $category['id'] ? 'is-active' : '' ?>" href="index.php?category=<?= (int) $category['id'] ?>" <?= $selectedCategory === (int) $category['id'] ? 'aria-current="page"' : '' ?>>
                        <?= e((string) $category['name']) ?>
                    </a>
                <?php endforeach; ?>
                <a class="<?= $showUncategorized ? 'is-active' : '' ?>" href="index.php?category=none" <?= $showUncategorized ? 'aria-current="page"' : '' ?>>未分類</a>
            </nav>

            <?php if ($todos === []): ?>
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-3" aria-hidden="true">＋</div>
                    <p class="fw-bold mb-1">表示するTODOはありません。</p>
                    <span>上の入力欄から、新しいタスクを追加しましょう。</span>
                </div>
            <?php else: ?>
                <ul class="task-list list-unstyled mb-0">
                    <?php foreach ($todos as $todo): ?>
                        <li class="task-item <?= (int) $todo['is_completed'] === 1 ? 'is-completed' : '' ?>">
                            <form action="index.php" method="post">
                                <?= csrfInput() ?>
                                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                <input type="hidden" name="action" value="toggle_todo">
                                <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                                <button class="complete-button" type="submit" aria-label="<?= (int) $todo['is_completed'] === 1 ? '未完了に戻す' : '完了にする' ?>">
                                    <?= (int) $todo['is_completed'] === 1 ? '✓' : '' ?>
                                </button>
                            </form>
                            <div class="task-content">
                                <span class="task-title"><?= e((string) $todo['title']) ?></span>
                                <?php if ($todo['category_name'] !== null): ?>
                                    <span class="category-badge"><?= e((string) $todo['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="task-actions">
                                <a class="btn btn-sm btn-light" href="index.php?edit=<?= (int) $todo['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                                <form action="index.php" method="post">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                    <input type="hidden" name="action" value="delete_todo">
                                    <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                                    <button class="btn btn-sm btn-link text-danger" type="submit">削除</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <aside class="col-lg-4" aria-label="カテゴリ管理">
        <div class="content-card category-card p-3 p-md-4">
            <p class="section-kicker mb-1">ORGANIZE</p>
            <h2 class="h4 mb-3">カテゴリを管理</h2>
            <form action="index.php" method="post">
                <?= csrfInput() ?>
                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                <input type="hidden" name="action" value="<?= $editingCategory === null ? 'add_category' : 'update_category' ?>">
                <?php if ($editingCategory !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingCategory['id'] ?>">
                <?php endif; ?>
                <label class="form-label visually-hidden" for="category_name">カテゴリ名</label>
                <div class="input-group mb-2">
                    <input class="form-control" id="category_name" name="name" type="text" maxlength="40" placeholder="例：仕事" value="<?= $editingCategory === null ? '' : e((string) $editingCategory['name']) ?>" required>
                    <button class="btn btn-dark" type="submit"><?= $editingCategory === null ? '作成' : '保存' ?></button>
                </div>
                <?php if ($editingCategory !== null): ?>
                    <a class="btn btn-sm btn-link px-0 text-secondary" href="index.php">キャンセル</a>
                <?php endif; ?>
            </form>
            <ul class="category-list list-unstyled mb-0 mt-3">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="category-name" href="index.php?category=<?= (int) $category['id'] ?>"><?= e((string) $category['name']) ?></a>
                        <div class="d-flex gap-1">
                            <a class="btn btn-sm btn-light" href="index.php?edit_category=<?= (int) $category['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                            <form action="index.php" method="post">
                                <?= csrfInput() ?>
                                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                <button class="btn btn-sm btn-link text-danger" type="submit">削除</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
</div>
<?php renderFooter(); ?>

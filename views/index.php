<section class="summary-grid" aria-label="TODOの状況">
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

<div class="layout-grid">
    <section class="task-area" aria-label="TODO管理">
        <section class="panel composer-panel">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">TASK COMPOSER</p>
                    <h2><?= $editingTodo === null ? '新しいタスクを追加' : 'タスクを編集' ?></h2>
                </div>
                <?php if ($editingTodo !== null): ?>
                    <a class="button button-quiet" href="index.php<?= $returnCategory === '' ? '' : '?category=' . rawurlencode($returnCategory) ?>">編集をやめる</a>
                <?php endif; ?>
            </div>
            <form action="index.php" method="post">
                <?= csrfInput() ?>
                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                <input type="hidden" name="action" value="<?= $editingTodo === null ? 'add_todo' : 'update_todo' ?>">
                <?php if ($editingTodo !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingTodo['id'] ?>">
                <?php endif; ?>
                <div class="composer-fields">
                    <label class="screen-reader-only" for="title">TODO</label>
                    <input class="field field-title" id="title" name="title" type="text" maxlength="100" placeholder="例：企画書の下書きを作成" value="<?= $editingTodo === null ? '' : e((string) $editingTodo['title']) ?>" required autofocus>
                    <label class="screen-reader-only" for="category_id">カテゴリ</label>
                    <select class="field field-category" id="category_id" name="category_id">
                        <option value="">カテゴリなし</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= $editingTodo !== null && (int) $editingTodo['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                <?= e((string) $category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button button-primary" type="submit"><?= $editingTodo === null ? '追加' : '保存' ?></button>
                </div>
            </form>
        </section>

        <?php if (is_string($message) && $message !== ''): ?>
            <p class="notice" role="status"><?= e($message) ?></p>
        <?php endif; ?>

        <section class="panel task-panel" aria-label="TODO一覧">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">YOUR TASKS</p>
                    <h2>タスク一覧</h2>
                </div>
                <span class="task-count"><?= count($todos) ?> 件</span>
            </div>
            <nav class="filter-nav" aria-label="カテゴリで絞り込む">
                <a class="<?= $selectedCategory === null && !$showUncategorized ? 'is-active' : '' ?>" href="index.php" <?= $selectedCategory === null && !$showUncategorized ? 'aria-current="page"' : '' ?>>すべて</a>
                <?php foreach ($categories as $category): ?>
                    <a class="<?= $selectedCategory === (int) $category['id'] ? 'is-active' : '' ?>" href="index.php?category=<?= (int) $category['id'] ?>" <?= $selectedCategory === (int) $category['id'] ? 'aria-current="page"' : '' ?>>
                        <?= e((string) $category['name']) ?>
                    </a>
                <?php endforeach; ?>
                <a class="<?= $showUncategorized ? 'is-active' : '' ?>" href="index.php?category=none" <?= $showUncategorized ? 'aria-current="page"' : '' ?>>未分類</a>
            </nav>

            <?php if ($todos === []): ?>
                <div class="empty-state">
                    <div class="empty-icon" aria-hidden="true">＋</div>
                    <p>表示するTODOはありません。</p>
                    <span>上の入力欄から、新しいタスクを追加しましょう。</span>
                </div>
            <?php else: ?>
                <ul class="task-list">
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
                                <a class="button button-quiet button-small" href="index.php?edit=<?= (int) $todo['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                                <form action="index.php" method="post">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                    <input type="hidden" name="action" value="delete_todo">
                                    <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                                    <button class="button button-danger button-small" type="submit">削除</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </section>

    <aside class="category-area" aria-label="カテゴリ管理">
        <section class="panel category-panel">
            <p class="section-kicker">ORGANIZE</p>
            <h2>カテゴリを管理</h2>
            <form class="category-form" action="index.php" method="post">
                <?= csrfInput() ?>
                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                <input type="hidden" name="action" value="<?= $editingCategory === null ? 'add_category' : 'update_category' ?>">
                <?php if ($editingCategory !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingCategory['id'] ?>">
                <?php endif; ?>
                <label class="screen-reader-only" for="category_name">カテゴリ名</label>
                <input class="field" id="category_name" name="name" type="text" maxlength="40" placeholder="例：仕事" value="<?= $editingCategory === null ? '' : e((string) $editingCategory['name']) ?>" required>
                <button class="button button-dark" type="submit"><?= $editingCategory === null ? '作成' : '保存' ?></button>
                <?php if ($editingCategory !== null): ?>
                    <a class="button button-link" href="index.php">キャンセル</a>
                <?php endif; ?>
            </form>
            <ul class="category-list">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="category-name" href="index.php?category=<?= (int) $category['id'] ?>"><?= e((string) $category['name']) ?></a>
                        <div class="category-actions">
                            <a class="button button-quiet button-small" href="index.php?edit_category=<?= (int) $category['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                            <form action="index.php" method="post">
                                <?= csrfInput() ?>
                                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                <button class="button button-danger button-small" type="submit">削除</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>
</div>
<?php renderFooter(); ?>

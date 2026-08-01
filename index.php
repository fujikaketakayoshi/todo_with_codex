<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/model.php';
require_once __DIR__ . '/lib/controller.php';
require_once __DIR__ . '/lib/actions.php';
require_once __DIR__ . '/lib/view.php';

initializeSession();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostAction();
}

$categories = findAllCategories();
$filter = $_GET['category'] ?? '';
$selectedCategory = is_string($filter) ? requestPositiveInt('category', $_GET) : null;
$showUncategorized = $filter === 'none';
if ($selectedCategory !== null && findCategory($selectedCategory) === null) {
    $selectedCategory = null;
}
$todos = findAllTodos($selectedCategory, $showUncategorized);
$message = $_GET['message'] ?? '';
$editingTodo = ($id = requestPositiveInt('edit', $_GET)) === null ? null : findTodo($id);
$editingCategory = ($id = requestPositiveInt('edit_category', $_GET)) === null ? null : findCategory($id);
$returnCategory = $showUncategorized ? 'none' : ($selectedCategory === null ? '' : (string) $selectedCategory);
$pageTitle = $showUncategorized ? '未分類のTODO' : ($selectedCategory === null ? 'TODO リスト' : (string) findCategory($selectedCategory)['name'] . ' のTODO');

renderHeader($pageTitle);
?>
<div class="app-grid">
    <section class="todo-panel" aria-label="TODO管理">
        <nav class="category-filter" aria-label="カテゴリで絞り込む">
            <a class="<?= $selectedCategory === null && !$showUncategorized ? 'is-active' : '' ?>" href="index.php">すべて</a>
            <?php foreach ($categories as $category): ?><a class="<?= $selectedCategory === (int) $category['id'] ? 'is-active' : '' ?>" href="index.php?category=<?= (int) $category['id'] ?>"><?= e((string) $category['name']) ?></a><?php endforeach; ?>
            <a class="<?= $showUncategorized ? 'is-active' : '' ?>" href="index.php?category=none">未分類</a>
        </nav>
        <form class="add-form" action="index.php" method="post">
            <?= csrfInput() ?><input type="hidden" name="return_category" value="<?= e($returnCategory) ?>"><input type="hidden" name="action" value="<?= $editingTodo === null ? 'add_todo' : 'update_todo' ?>">
            <?php if ($editingTodo !== null): ?><input type="hidden" name="id" value="<?= (int) $editingTodo['id'] ?>"><?php endif; ?>
            <label class="sr-only" for="title">TODO</label><input id="title" name="title" type="text" maxlength="120" placeholder="新しいTODOを入力" value="<?= $editingTodo === null ? '' : e((string) $editingTodo['title']) ?>" required autofocus>
            <label class="sr-only" for="category_id">カテゴリ</label><select id="category_id" name="category_id"><option value="">カテゴリなし</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $editingTodo !== null && (int) $editingTodo['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e((string) $category['name']) ?></option><?php endforeach; ?></select>
            <button type="submit"><?= $editingTodo === null ? '追加' : '保存' ?></button>
            <?php if ($editingTodo !== null): ?><a class="cancel" href="index.php<?= $returnCategory === '' ? '' : '?category=' . rawurlencode($returnCategory) ?>">キャンセル</a><?php endif; ?>
        </form>
        <?php if (is_string($message) && $message !== ''): ?><p class="notice" role="status"><?= e($message) ?></p><?php endif; ?>
        <section aria-label="TODO一覧">
            <?php if ($todos === []): ?><div class="empty-state"><p>表示するTODOはありません。</p><span>新しいタスクを追加してみましょう。</span></div>
            <?php else: ?><ul class="todo-list"><?php foreach ($todos as $todo): ?><li class="todo <?= (int) $todo['is_completed'] === 1 ? 'is-completed' : '' ?>">
                <form action="index.php" method="post" class="toggle-form"><?= csrfInput() ?><input type="hidden" name="return_category" value="<?= e($returnCategory) ?>"><input type="hidden" name="action" value="toggle_todo"><input type="hidden" name="id" value="<?= (int) $todo['id'] ?>"><button class="check" type="submit" aria-label="<?= (int) $todo['is_completed'] === 1 ? '未完了に戻す' : '完了にする' ?>"><?= (int) $todo['is_completed'] === 1 ? '✓' : '' ?></button></form>
                <span class="todo-title"><?= e((string) $todo['title']) ?><?php if ($todo['category_name'] !== null): ?><small class="category-tag"><?= e((string) $todo['category_name']) ?></small><?php endif; ?></span>
                <a class="edit" href="index.php?edit=<?= (int) $todo['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                <form action="index.php" method="post"><?= csrfInput() ?><input type="hidden" name="return_category" value="<?= e($returnCategory) ?>"><input type="hidden" name="action" value="delete_todo"><input type="hidden" name="id" value="<?= (int) $todo['id'] ?>"><button class="delete" type="submit">削除</button></form>
            </li><?php endforeach; ?></ul><?php endif; ?>
        </section>
    </section>
    <aside class="category-panel" aria-label="カテゴリ管理">
        <h2>カテゴリ</h2>
        <form class="category-form" action="index.php" method="post"><?= csrfInput() ?><input type="hidden" name="return_category" value="<?= e($returnCategory) ?>"><input type="hidden" name="action" value="<?= $editingCategory === null ? 'add_category' : 'update_category' ?>"><?php if ($editingCategory !== null): ?><input type="hidden" name="id" value="<?= (int) $editingCategory['id'] ?>"><?php endif; ?><label class="sr-only" for="category_name">カテゴリ名</label><input id="category_name" name="name" type="text" maxlength="40" placeholder="新しいカテゴリ" value="<?= $editingCategory === null ? '' : e((string) $editingCategory['name']) ?>" required><button type="submit"><?= $editingCategory === null ? '作成' : '保存' ?></button><?php if ($editingCategory !== null): ?><a class="cancel" href="index.php">キャンセル</a><?php endif; ?></form>
        <ul class="category-list"><?php foreach ($categories as $category): ?><li><span><?= e((string) $category['name']) ?></span><a class="edit" href="index.php?edit_category=<?= (int) $category['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a><form action="index.php" method="post"><?= csrfInput() ?><input type="hidden" name="return_category" value="<?= e($returnCategory) ?>"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="delete" type="submit">削除</button></form></li><?php endforeach; ?></ul>
    </aside>
</div>
<?php renderFooter(); ?>

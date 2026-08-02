<section class="mb-7 grid gap-3 sm:grid-cols-3" aria-label="TODOの状況">
    <article class="rounded-2xl bg-emerald-800 p-5 text-white shadow-sm">
        <span class="block text-sm font-bold text-emerald-100">すべてのTODO</span>
        <strong class="my-1 block text-3xl font-bold"><?= $todoSummary['total'] ?></strong>
        <small class="text-sm font-semibold text-emerald-100">登録済みのタスク</small>
    </article>
    <article class="rounded-2xl border border-stone-200 bg-white/85 p-5 shadow-sm">
        <span class="block text-sm font-bold text-slate-500">未完了</span>
        <strong class="my-1 block text-3xl font-bold text-slate-900"><?= $todoSummary['remaining'] ?></strong>
        <small class="text-sm font-semibold text-slate-500">次に取り組むタスク</small>
    </article>
    <article class="rounded-2xl border border-stone-200 bg-white/85 p-5 shadow-sm">
        <span class="block text-sm font-bold text-slate-500">完了</span>
        <strong class="my-1 block text-3xl font-bold text-slate-900"><?= $todoSummary['completed'] ?></strong>
        <small class="text-sm font-semibold text-slate-500">片付いたタスク</small>
    </article>
</section>

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
    <section class="grid gap-5" aria-label="TODO管理">
        <section class="rounded-2xl border border-stone-200 bg-white/90 p-5 shadow-sm sm:p-7">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="mb-1 text-xs font-extrabold tracking-[0.14em] text-emerald-700">TASK COMPOSER</p>
                    <h2 class="font-serif text-xl font-bold text-slate-900"><?= $editingTodo === null ? '新しいタスクを追加' : 'タスクを編集' ?></h2>
                </div>
                <?php if ($editingTodo !== null): ?>
                    <a class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="index.php<?= $returnCategory === '' ? '' : '?category=' . rawurlencode($returnCategory) ?>">編集をやめる</a>
                <?php endif; ?>
            </div>
            <form action="index.php" method="post">
                <?= csrfInput() ?>
                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                <input type="hidden" name="action" value="<?= $editingTodo === null ? 'add_todo' : 'update_todo' ?>">
                <?php if ($editingTodo !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingTodo['id'] ?>">
                <?php endif; ?>
                <div class="grid gap-2 md:grid-cols-[minmax(0,1fr)_9.5rem_auto]">
                    <div>
                        <label class="sr-only" for="title">TODO</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3 py-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-2 focus:outline-emerald-200" id="title" name="title" type="text" placeholder="例：企画書の下書きを作成" value="<?= $editingTodo === null ? '' : e((string) $editingTodo['title']) ?>" aria-describedby="title-hint" required autofocus>
                        <small class="ml-1 mt-1.5 block text-xs font-medium text-slate-500" id="title-hint">最大100文字</small>
                    </div>
                    <div>
                        <label class="sr-only" for="category_id">カテゴリ</label>
                        <select class="w-full rounded-xl border border-stone-300 bg-white px-3 py-3 text-slate-700 focus:border-emerald-600 focus:outline-2 focus:outline-emerald-200" id="category_id" name="category_id">
                            <option value="">カテゴリなし</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $editingTodo !== null && (int) $editingTodo['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e((string) $category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="rounded-xl bg-emerald-800 px-5 py-3 font-bold text-white transition hover:bg-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700" type="submit"><?= $editingTodo === null ? '追加' : '保存' ?></button>
                </div>
            </form>
        </section>

        <?php if ($message !== null): ?>
            <p class="rounded-xl border px-4 py-3 text-sm font-bold <?= $message['type'] === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' ?>" role="<?= $message['type'] === 'error' ? 'alert' : 'status' ?>">
                <?= e($message['message']) ?>
            </p>
        <?php endif; ?>

        <section class="rounded-2xl border border-stone-200 bg-white/90 p-5 shadow-sm sm:p-7" aria-label="TODO一覧">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="mb-1 text-xs font-extrabold tracking-[0.14em] text-emerald-700">YOUR TASKS</p>
                    <h2 class="font-serif text-xl font-bold text-slate-900">タスク一覧</h2>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-extrabold text-emerald-800"><?= count($todos) ?> 件</span>
            </div>
            <nav class="mb-5 flex flex-wrap gap-2" aria-label="カテゴリで絞り込む">
                <a class="rounded-full px-3 py-2 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 <?= $selectedCategory === null && !$showUncategorized ? 'bg-emerald-800 text-white' : 'bg-stone-100 text-slate-600 hover:bg-stone-200' ?>" href="index.php" <?= $selectedCategory === null && !$showUncategorized ? 'aria-current="page"' : '' ?>>すべて</a>
                <?php foreach ($categories as $category): ?>
                    <a class="rounded-full px-3 py-2 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 <?= $selectedCategory === (int) $category['id'] ? 'bg-emerald-800 text-white' : 'bg-stone-100 text-slate-600 hover:bg-stone-200' ?>" href="index.php?category=<?= (int) $category['id'] ?>" <?= $selectedCategory === (int) $category['id'] ? 'aria-current="page"' : '' ?>><?= e((string) $category['name']) ?></a>
                <?php endforeach; ?>
                <a class="rounded-full px-3 py-2 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 <?= $showUncategorized ? 'bg-emerald-800 text-white' : 'bg-stone-100 text-slate-600 hover:bg-stone-200' ?>" href="index.php?category=none" <?= $showUncategorized ? 'aria-current="page"' : '' ?>>未分類</a>
            </nav>

            <?php if ($todos === []): ?>
                <div class="rounded-xl border border-dashed border-stone-300 px-5 py-12 text-center text-slate-500">
                    <div class="mx-auto mb-3 grid size-10 place-items-center rounded-full bg-emerald-100 text-xl text-emerald-800" aria-hidden="true">＋</div>
                    <p class="mb-1 font-bold text-slate-800">表示するTODOはありません。</p>
                    <span class="text-sm">上の入力欄から、新しいタスクを追加しましょう。</span>
                </div>
            <?php else: ?>
                <ul class="grid gap-2.5">
                    <?php foreach ($todos as $todo): ?>
                        <li class="flex flex-wrap items-center gap-3 rounded-xl border border-stone-200 bg-white p-3 transition hover:-translate-y-px hover:shadow-md sm:flex-nowrap <?= (int) $todo['is_completed'] === 1 ? 'opacity-65' : '' ?>">
                            <form action="index.php" method="post">
                                <?= csrfInput() ?>
                                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                <input type="hidden" name="action" value="toggle_todo">
                                <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                                <button class="grid size-7 place-items-center rounded-full border-2 <?= (int) $todo['is_completed'] === 1 ? 'border-emerald-800 bg-emerald-800 text-white' : 'border-stone-400 bg-white text-transparent' ?> focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" type="submit" aria-label="<?= (int) $todo['is_completed'] === 1 ? '未完了に戻す' : '完了にする' ?>">✓</button>
                            </form>
                            <div class="min-w-0 flex-1">
                                <span class="break-words font-bold <?= (int) $todo['is_completed'] === 1 ? 'text-slate-400 line-through' : 'text-slate-800' ?>"><?= e((string) $todo['title']) ?></span>
                                <?php if ($todo['category_name'] !== null): ?><span class="ml-2 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-extrabold text-emerald-800"><?= e((string) $todo['category_name']) ?></span><?php endif; ?>
                            </div>
                            <div class="flex w-full gap-1.5 sm:w-auto">
                                <a class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-extrabold text-emerald-800 transition hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="index.php?edit=<?= (int) $todo['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                                <form action="index.php" method="post">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                                    <input type="hidden" name="action" value="delete_todo">
                                    <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                                    <button class="rounded-lg px-2 py-2 text-xs font-extrabold text-red-700 transition hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" type="submit">削除</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </section>

    <aside class="lg:sticky lg:top-6" aria-label="カテゴリ管理">
        <section class="rounded-2xl border border-stone-200 bg-white/90 p-5 shadow-sm sm:p-6">
            <p class="mb-1 text-xs font-extrabold tracking-[0.14em] text-emerald-700">ORGANIZE</p>
            <h2 class="mb-5 font-serif text-xl font-bold text-slate-900">カテゴリを管理</h2>
            <form class="grid grid-cols-[minmax(0,1fr)_auto] gap-2" action="index.php" method="post">
                <?= csrfInput() ?>
                <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                <input type="hidden" name="action" value="<?= $editingCategory === null ? 'add_category' : 'update_category' ?>">
                <?php if ($editingCategory !== null): ?><input type="hidden" name="id" value="<?= (int) $editingCategory['id'] ?>"><?php endif; ?>
                <label class="sr-only" for="category_name">カテゴリ名</label>
                <input class="min-w-0 rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-2 focus:outline-emerald-200" id="category_name" name="name" type="text" maxlength="40" placeholder="例：仕事" value="<?= $editingCategory === null ? '' : e((string) $editingCategory['name']) ?>" required>
                <button class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-700" type="submit"><?= $editingCategory === null ? '作成' : '保存' ?></button>
                <?php if ($editingCategory !== null): ?><a class="col-span-2 pt-1 text-xs font-bold text-slate-500 underline hover:text-slate-800" href="index.php">キャンセル</a><?php endif; ?>
            </form>
            <ul class="mt-5 grid gap-1">
                <?php foreach ($categories as $category): ?>
                    <li class="flex items-center gap-2 border-t border-stone-200 py-2.5">
                        <a class="min-w-0 flex-1 break-words text-sm font-extrabold text-slate-800 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="index.php?category=<?= (int) $category['id'] ?>"><?= e((string) $category['name']) ?></a>
                        <a class="rounded-md bg-emerald-50 px-2 py-1.5 text-xs font-extrabold text-emerald-800 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="index.php?edit_category=<?= (int) $category['id'] ?><?= $returnCategory === '' ? '' : '&category=' . rawurlencode($returnCategory) ?>">編集</a>
                        <form action="index.php" method="post">
                            <?= csrfInput() ?>
                            <input type="hidden" name="return_category" value="<?= e($returnCategory) ?>">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                            <button class="rounded-md px-1.5 py-1.5 text-xs font-extrabold text-red-700 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" type="submit">削除</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>
</div>
<?php renderFooter(); ?>

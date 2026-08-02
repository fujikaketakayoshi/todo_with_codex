<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrfInput(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

function renderHeader(string $title = 'TODO アプリ'): void
{
    ?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-w-80 bg-stone-50 text-slate-800 antialiased">
    <main class="mx-auto w-full max-w-6xl px-5 py-8 sm:px-8 sm:py-12">
        <header class="mb-8 flex items-center justify-between gap-6 sm:mb-10">
            <div>
                <p class="mb-2 text-xs font-extrabold tracking-[0.16em] text-emerald-700">MY TASKS</p>
                <h1 class="font-serif text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"><?= e($title) ?></h1>
                <p class="mt-2 text-sm text-slate-500 sm:text-base">今日のやることを、ひとつずつ片付けよう。</p>
            </div>
            <div class="grid size-12 rotate-[-10deg] place-items-center rounded-full rounded-bl-md bg-emerald-800 text-xl font-extrabold text-white shadow-[5px_5px_0_#fed7aa] sm:size-14" aria-hidden="true">✓</div>
        </header>
    <?php
}

function renderFooter(): void
{
    ?>
    </main>
</body>
</html>
    <?php
}

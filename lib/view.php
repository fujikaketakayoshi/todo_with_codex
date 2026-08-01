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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container py-5">
        <header class="mb-4">
            <p class="eyebrow mb-1">MY TASKS</p>
            <h1 class="h2 mb-2"><?= e($title) ?></h1>
            <p class="subtitle mb-0">やることを整理して、気持ちよく片付けよう。</p>
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

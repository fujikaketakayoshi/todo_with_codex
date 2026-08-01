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
<body>
    <main class="container">
        <header>
            <p class="eyebrow">MY TASKS</p>
            <h1><?= e($title) ?></h1>
            <p class="subtitle">やることを整理して、気持ちよく片付けよう。</p>
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

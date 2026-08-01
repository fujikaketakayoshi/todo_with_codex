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
<body class="app-body">
    <main class="container py-4 py-md-5">
        <header class="app-header mb-4 mb-md-5">
            <div>
                <p class="eyebrow mb-2">MY TASKS</p>
                <h1 class="display-6 fw-bold mb-2"><?= e($title) ?></h1>
                <p class="subtitle mb-0">今日のやることを、ひとつずつ片付けよう。</p>
            </div>
            <div class="header-mark" aria-hidden="true">✓</div>
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

<?php
declare(strict_types=1);

$testDatabasePath = sys_get_temp_dir() . '/todo_with_codex_test.sqlite';
if (is_file($testDatabasePath)) {
    unlink($testDatabasePath);
}

putenv('TODO_DATABASE_PATH=' . $testDatabasePath);

require_once __DIR__ . '/../lib/model.php';
require_once __DIR__ . '/../lib/controller.php';

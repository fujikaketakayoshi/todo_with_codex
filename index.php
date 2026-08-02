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
$selectedCategoryData = $selectedCategory === null ? null : findCategory($selectedCategory);
$selectedCategory = $selectedCategoryData === null ? null : $selectedCategory;
$todos = findAllTodos($selectedCategory, $showUncategorized);
$allTodos = findAllTodos();
$todoSummary = [
    'total' => count($allTodos),
    'remaining' => count(array_filter($allTodos, static fn(array $todo): bool => (int) $todo['is_completed'] === 0)),
    'completed' => count(array_filter($allTodos, static fn(array $todo): bool => (int) $todo['is_completed'] === 1)),
];
$message = pullFlashMessage();
$editingTodo = ($id = requestPositiveInt('edit', $_GET)) === null ? null : findTodo($id);
$editingCategory = ($id = requestPositiveInt('edit_category', $_GET)) === null ? null : findCategory($id);
$returnCategory = $showUncategorized ? 'none' : ($selectedCategory === null ? '' : (string) $selectedCategory);
$pageTitle = $showUncategorized
    ? '未分類のTODO'
    : ($selectedCategoryData === null ? 'TODO リスト' : $selectedCategoryData['name'] . ' のTODO');

renderHeader($pageTitle);
require __DIR__ . '/views/index.php';

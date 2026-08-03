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

$tags = findAllTags();
$filter = $_GET['tag'] ?? '';
$selectedTag = is_string($filter) ? requestPositiveInt('tag', $_GET) : null;
$showUntagged = $filter === 'none';
$selectedTagData = $selectedTag === null ? null : findTag($selectedTag);
$selectedTag = $selectedTagData === null ? null : $selectedTag;
$todos = findAllTodos($selectedTag, $showUntagged);
$allTodos = findAllTodos();
$todoSummary = [
    'total' => count($allTodos),
    'remaining' => count(array_filter($allTodos, static fn(array $todo): bool => (int) $todo['is_completed'] === 0)),
    'completed' => count(array_filter($allTodos, static fn(array $todo): bool => (int) $todo['is_completed'] === 1)),
];
$message = pullFlashMessage();
$editingTodo = ($id = requestPositiveInt('edit', $_GET)) === null ? null : findTodo($id);
$editingTag = ($id = requestPositiveInt('edit_tag', $_GET)) === null ? null : findTag($id);
$returnTag = $showUntagged ? 'none' : ($selectedTag === null ? '' : (string) $selectedTag);
$pageTitle = $showUntagged
    ? 'タグなしのTODO'
    : ($selectedTagData === null ? 'TODO リスト' : $selectedTagData['name'] . ' のTODO');

renderHeader($pageTitle);
require __DIR__ . '/views/index.php';

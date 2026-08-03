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
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$searchStatus = $_GET['status'] ?? 'all';
$searchStatus = in_array($searchStatus, ['all', 'completed', 'incomplete'], true) ? $searchStatus : 'all';
$selectedTagIds = [];
foreach (is_array($_GET['tags'] ?? null) ? $_GET['tags'] : [] as $tagId) {
    $validTagId = filter_var($tagId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (is_int($validTagId) && findTag($validTagId) !== null) {
        $selectedTagIds[] = $validTagId;
    }
}
$selectedTagIds = array_values(array_unique($selectedTagIds));
$todos = findAllTodos(null, false, $searchQuery, $searchStatus, $selectedTagIds);
$selectedTag = null;
$showUntagged = false;
$returnTag = '';
$allTodos = findAllTodos();
$todoSummary = [
    'total' => count($allTodos),
    'remaining' => count(array_filter($allTodos, static fn(array $todo): bool => (int) $todo['is_completed'] === 0)),
    'completed' => count(array_filter($allTodos, static fn(array $todo): bool => (int) $todo['is_completed'] === 1)),
];
$message = pullFlashMessage();
$editingTodo = ($id = requestPositiveInt('edit', $_GET)) === null ? null : findTodo($id);
$editingTodoInput = $editingTodo === null
    ? ''
    : $editingTodo['title'] . ($editingTodo['tag_names'] === null ? '' : ' #' . str_replace(' • ', ' #', $editingTodo['tag_names']));
$pageTitle = 'TODO リスト';

renderHeader($pageTitle);
require __DIR__ . '/views/index.php';

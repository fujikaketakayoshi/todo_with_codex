<?php
declare(strict_types=1);

const TODO_TITLE_MAXIMUM_LENGTH = 100;

function requireExistingTodo(): int
{
    $id = requestPositiveInt('id', $_POST);
    if ($id === null || findTodo($id) === null) {
        redirect('対象のTODOが見つかりません。', returnFilterParameters());
    }
    return $id;
}

function requireExistingCategory(): int
{
    $id = requestPositiveInt('id', $_POST);
    if ($id === null || findCategory($id) === null) {
        redirect('対象のカテゴリが見つかりません。', returnFilterParameters());
    }
    return $id;
}

function handleTodoAction(string $action): never
{
    $parameters = returnFilterParameters();
    if ($action === 'add_todo') {
        createTodo(requiredText('title', 'TODO', TODO_TITLE_MAXIMUM_LENGTH), requestCategoryId());
        redirect('TODOを追加しました。', $parameters);
    }
    $id = requireExistingTodo();
    if ($action === 'update_todo') {
        updateTodo($id, requiredText('title', 'TODO', TODO_TITLE_MAXIMUM_LENGTH), requestCategoryId());
        redirect('TODOを編集しました。', $parameters);
    }
    if ($action === 'toggle_todo') {
        toggleTodo($id);
        redirect('TODOを更新しました。', $parameters);
    }
    deleteTodo($id);
    redirect('TODOを削除しました。', $parameters);
}

function handleCategoryAction(string $action): never
{
    $parameters = returnFilterParameters();
    if ($action === 'add_category') {
        try {
            createCategory(requiredText('name', 'カテゴリ名', 40));
            redirect('カテゴリを作成しました。', $parameters);
        } catch (PDOException $exception) {
            redirect('同じ名前のカテゴリがすでにあります。', $parameters);
        }
    }
    $id = requireExistingCategory();
    if ($action === 'update_category') {
        try {
            updateCategory($id, requiredText('name', 'カテゴリ名', 40));
            redirect('カテゴリを編集しました。', $parameters);
        } catch (PDOException $exception) {
            redirect('同じ名前のカテゴリがすでにあります。', $parameters);
        }
    }
    deleteCategory($id);
    redirect('カテゴリを削除しました。TODOのカテゴリ指定も解除されました。', $parameters);
}

function handlePostAction(): never
{
    validateCsrf();
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['add_todo', 'update_todo', 'toggle_todo', 'delete_todo'], true)) {
        handleTodoAction($action);
    }
    if (in_array($action, ['add_category', 'update_category', 'delete_category'], true)) {
        handleCategoryAction($action);
    }
    redirect();
}

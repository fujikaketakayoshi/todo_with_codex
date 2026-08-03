<?php
declare(strict_types=1);

const TODO_TITLE_MAXIMUM_LENGTH = 100;
const TAG_NAME_MAXIMUM_LENGTH = 30;

function requireExistingTodo(): int
{
    $id = requestPositiveInt('id', $_POST);
    if ($id === null || findTodo($id) === null) {
        redirect('対象のTODOが見つかりません。', returnFilterParameters(), 'error');
    }
    return $id;
}

function requireExistingTag(): int
{
    $id = requestPositiveInt('id', $_POST);
    if ($id === null || findTag($id) === null) {
        redirect('対象のタグが見つかりません。', returnFilterParameters(), 'error');
    }
    return $id;
}

function handleTodoAction(string $action): never
{
    $parameters = returnFilterParameters();
    if ($action === 'add_todo') {
        createTodo(requiredText('title', 'TODO', TODO_TITLE_MAXIMUM_LENGTH), requestTagIds());
        redirect('TODOを追加しました。', $parameters);
    }
    $id = requireExistingTodo();
    if ($action === 'update_todo') {
        updateTodo($id, requiredText('title', 'TODO', TODO_TITLE_MAXIMUM_LENGTH), requestTagIds());
        redirect('TODOを編集しました。', $parameters);
    }
    if ($action === 'toggle_todo') {
        toggleTodo($id);
        redirect('TODOを更新しました。', $parameters);
    }
    deleteTodo($id);
    redirect('TODOを削除しました。', $parameters);
}

function handleTagAction(string $action): never
{
    $parameters = returnFilterParameters();
    if ($action === 'add_tag') {
        try {
            createTag(requiredText('name', 'タグ名', TAG_NAME_MAXIMUM_LENGTH));
            redirect('タグを作成しました。', $parameters);
        } catch (PDOException $exception) {
            redirect('同じ名前のタグがすでにあります。', $parameters, 'error');
        }
    }
    $id = requireExistingTag();
    if ($action === 'update_tag') {
        try {
            updateTag($id, requiredText('name', 'タグ名', TAG_NAME_MAXIMUM_LENGTH));
            redirect('タグを編集しました。', $parameters);
        } catch (PDOException $exception) {
            redirect('同じ名前のタグがすでにあります。', $parameters, 'error');
        }
    }
    deleteTag($id);
    redirect('タグを削除しました。TODOとの関連付けも解除されました。', $parameters);
}

function handlePostAction(): never
{
    validateCsrf();
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['add_todo', 'update_todo', 'toggle_todo', 'delete_todo'], true)) {
        handleTodoAction($action);
    }
    if (in_array($action, ['add_tag', 'update_tag', 'delete_tag'], true)) {
        handleTagAction($action);
    }
    redirect();
}

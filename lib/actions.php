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

/** @return array{title: string, tag_names: list<string>} */
function parseTodoInput(string $input): array
{
    $titleParts = [];
    $tagNames = [];
    $words = preg_split('/\s+/u', trim($input), -1, PREG_SPLIT_NO_EMPTY) ?: [];

    foreach ($words as $word) {
        if (preg_match('/^#([^#\s]+)$/u', $word, $matches) === 1) {
            $tagName = $matches[1];
            if (!isWithinMaximumLength($tagName, TAG_NAME_MAXIMUM_LENGTH)) {
                redirect('タグ名は' . TAG_NAME_MAXIMUM_LENGTH . '文字以内で入力してください。', [], 'error');
            }
            $tagNames[] = $tagName;
            continue;
        }
        $titleParts[] = $word;
    }

    $title = implode(' ', $titleParts);
    if ($title === '') {
        redirect('TODOを入力してください。', [], 'error');
    }

    return ['title' => $title, 'tag_names' => array_values(array_unique($tagNames))];
}

function handleTodoAction(string $action): never
{
    $parameters = returnFilterParameters();
    if ($action === 'add_todo') {
        $todoInput = parseTodoInput(requiredText('title', 'TODO', TODO_TITLE_MAXIMUM_LENGTH));
        createTodo($todoInput['title'], $todoInput['tag_names']);
        redirect('TODOを追加しました。', $parameters);
    }
    $id = requireExistingTodo();
    if ($action === 'update_todo') {
        $todoInput = parseTodoInput(requiredText('title', 'TODO', TODO_TITLE_MAXIMUM_LENGTH));
        updateTodo($id, $todoInput['title'], $todoInput['tag_names']);
        redirect('TODOを編集しました。', $parameters);
    }
    if ($action === 'toggle_todo') {
        toggleTodo($id);
        redirect('TODOを更新しました。', $parameters);
    }
    deleteTodo($id);
    redirect('TODOを削除しました。', $parameters);
}

function handlePostAction(): never
{
    validateCsrf();
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['add_todo', 'update_todo', 'toggle_todo', 'delete_todo'], true)) {
        handleTodoAction($action);
    }
    redirect();
}

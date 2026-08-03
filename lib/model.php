<?php
declare(strict_types=1);

function databasePath(): string
{
    $testDatabasePath = getenv('TODO_DATABASE_PATH');

    return $testDatabasePath === false || $testDatabasePath === ''
        ? __DIR__ . '/../data/todos.sqlite'
        : $testDatabasePath;
}

function todoDatabase(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databasePath = databasePath();
    $dataDirectory = dirname($databasePath);
    if (!is_dir($dataDirectory)) {
        mkdir($dataDirectory, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    migrateCategoriesToTags($pdo);
    createTagTable($pdo);
    createTodoTable($pdo);
    migrateTodoCategoryToTags($pdo);
    createTodoTagTable($pdo);

    return $pdo;
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $statement = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :name");
    $statement->execute([':name' => $tableName]);

    return $statement->fetchColumn() !== false;
}

function tableHasColumn(PDO $pdo, string $tableName, string $columnName): bool
{
    $columns = $pdo->query('PRAGMA table_info(' . $tableName . ')')->fetchAll(PDO::FETCH_ASSOC);

    return array_filter(
        $columns,
        static fn(array $column): bool => $column['name'] === $columnName
    ) !== [];
}

function migrateCategoriesToTags(PDO $pdo): void
{
    if (tableExists($pdo, 'categories') && !tableExists($pdo, 'tags')) {
        $pdo->exec('ALTER TABLE categories RENAME TO tags');
    }
}

function createTagTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function createTodoTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS todos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            is_completed INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function createTodoTagTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS todo_tags (
            todo_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            PRIMARY KEY (todo_id, tag_id),
            FOREIGN KEY (todo_id) REFERENCES todos(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )'
    );
}

function migrateTodoCategoryToTags(PDO $pdo): void
{
    if (!tableHasColumn($pdo, 'todos', 'category_id')) {
        return;
    }

    $pdo->beginTransaction();

    try {
        $pdo->exec('ALTER TABLE todos RENAME TO todos_legacy');
        createTodoTable($pdo);
        $pdo->exec(
            'INSERT INTO todos (id, title, is_completed, created_at)
             SELECT id, title, is_completed, created_at FROM todos_legacy'
        );
        createTodoTagTable($pdo);
        $pdo->exec(
            'INSERT OR IGNORE INTO todo_tags (todo_id, tag_id)
             SELECT id, category_id FROM todos_legacy WHERE category_id IS NOT NULL'
        );
        $pdo->exec('DROP TABLE todos_legacy');
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

/** @return list<array{id: int, title: string, is_completed: int, tag_names: ?string, created_at: string}> */
/** @param list<int> $tagIds
 *  @return list<array{id: int, title: string, is_completed: int, tag_names: ?string, created_at: string}> */
function findAllTodos(
    ?int $tagId = null,
    bool $onlyUntagged = false,
    string $query = '',
    string $status = 'all',
    array $tagIds = []
): array
{
    $sql = 'SELECT todos.id, todos.title, todos.is_completed,
                   GROUP_CONCAT(tags.name, \' • \') AS tag_names, todos.created_at
            FROM todos
            LEFT JOIN todo_tags ON todo_tags.todo_id = todos.id
            LEFT JOIN tags ON tags.id = todo_tags.tag_id';

    if ($tagId !== null) {
        $tagIds[] = $tagId;
    }

    $conditions = [];
    $parameters = [];
    if ($onlyUntagged) {
        $conditions[] = 'NOT EXISTS (
            SELECT 1 FROM todo_tags assigned_tags WHERE assigned_tags.todo_id = todos.id
        )';
    }
    if ($query !== '') {
        $conditions[] = "todos.title LIKE :query ESCAPE '\\'";
        $parameters[':query'] = '%' . addcslashes($query, '\\%_') . '%';
    }
    if ($status === 'completed') {
        $conditions[] = 'todos.is_completed = 1';
    } elseif ($status === 'incomplete') {
        $conditions[] = 'todos.is_completed = 0';
    }

    $tagIds = array_values(array_unique($tagIds));
    if ($tagIds !== []) {
        $placeholders = [];
        foreach ($tagIds as $index => $selectedTagId) {
            $placeholder = ':tag_' . $index;
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = $selectedTagId;
        }
        $conditions[] = '(SELECT COUNT(DISTINCT filtered_tags.tag_id) FROM todo_tags filtered_tags
            WHERE filtered_tags.todo_id = todos.id AND filtered_tags.tag_id IN (' . implode(', ', $placeholders) . ')) = ' . count($tagIds);
    }
    if ($conditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' GROUP BY todos.id ORDER BY todos.is_completed ASC, todos.id DESC';
    $statement = todoDatabase()->prepare($sql);
    $statement->execute($parameters);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/** @return array{id: int, title: string, is_completed: int, tag_names: ?string, tag_ids: list<int>, created_at: string}|null */
function findTodo(int $id): ?array
{
    $statement = todoDatabase()->prepare(
        'SELECT todos.id, todos.title, todos.is_completed,
                GROUP_CONCAT(tags.name, \' • \') AS tag_names, todos.created_at
         FROM todos
         LEFT JOIN todo_tags ON todo_tags.todo_id = todos.id
         LEFT JOIN tags ON tags.id = todo_tags.tag_id
         WHERE todos.id = :id
         GROUP BY todos.id'
    );
    $statement->execute([':id' => $id]);
    $todo = $statement->fetch(PDO::FETCH_ASSOC);

    if ($todo === false) {
        return null;
    }

    $todo['tag_ids'] = findTagIdsForTodo($id);

    return $todo;
}

/** @return list<int> */
function findTagIdsForTodo(int $todoId): array
{
    $statement = todoDatabase()->prepare('SELECT tag_id FROM todo_tags WHERE todo_id = :todo_id');
    $statement->execute([':todo_id' => $todoId]);

    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

/** @param list<string> $tagNames */
function createTodo(string $title, array $tagNames): void
{
    $pdo = todoDatabase();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare('INSERT INTO todos (title) VALUES (:title)');
        $statement->execute([':title' => $title]);
        replaceTodoTags($pdo, (int) $pdo->lastInsertId(), findOrCreateTagIds($pdo, $tagNames));
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

/** @param list<string> $tagNames */
function updateTodo(int $id, string $title, array $tagNames): void
{
    $pdo = todoDatabase();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare('UPDATE todos SET title = :title WHERE id = :id');
        $statement->execute([':id' => $id, ':title' => $title]);
        replaceTodoTags($pdo, $id, findOrCreateTagIds($pdo, $tagNames));
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

/** @param list<string> $tagNames
 *  @return list<int> */
function findOrCreateTagIds(PDO $pdo, array $tagNames): array
{
    $tagIds = [];
    $findStatement = $pdo->prepare('SELECT id FROM tags WHERE name = :name');
    $createStatement = $pdo->prepare('INSERT INTO tags (name) VALUES (:name)');

    foreach (array_unique($tagNames) as $tagName) {
        $findStatement->execute([':name' => $tagName]);
        $tagId = $findStatement->fetchColumn();
        if ($tagId === false) {
            $createStatement->execute([':name' => $tagName]);
            $tagId = $pdo->lastInsertId();
        }
        $tagIds[] = (int) $tagId;
    }

    return $tagIds;
}

/** @param list<int> $tagIds */
function replaceTodoTags(PDO $pdo, int $todoId, array $tagIds): void
{
    $deleteStatement = $pdo->prepare('DELETE FROM todo_tags WHERE todo_id = :todo_id');
    $deleteStatement->execute([':todo_id' => $todoId]);

    $insertStatement = $pdo->prepare('INSERT INTO todo_tags (todo_id, tag_id) VALUES (:todo_id, :tag_id)');
    foreach (array_unique($tagIds) as $tagId) {
        $insertStatement->execute([':todo_id' => $todoId, ':tag_id' => $tagId]);
    }
}

function toggleTodo(int $id): void
{
    $statement = todoDatabase()->prepare('UPDATE todos SET is_completed = CASE is_completed WHEN 1 THEN 0 ELSE 1 END WHERE id = :id');
    $statement->execute([':id' => $id]);
}

function deleteTodo(int $id): void
{
    $statement = todoDatabase()->prepare('DELETE FROM todos WHERE id = :id');
    $statement->execute([':id' => $id]);
}

/** @return list<array{id: int, name: string, created_at: string}> */
function findAllTags(): array
{
    return todoDatabase()->query('SELECT id, name, created_at FROM tags ORDER BY name COLLATE NOCASE ASC')->fetchAll(PDO::FETCH_ASSOC);
}

/** @return array{id: int, name: string, created_at: string}|null */
function findTag(int $id): ?array
{
    $statement = todoDatabase()->prepare('SELECT id, name, created_at FROM tags WHERE id = :id');
    $statement->execute([':id' => $id]);
    $tag = $statement->fetch(PDO::FETCH_ASSOC);

    return $tag === false ? null : $tag;
}

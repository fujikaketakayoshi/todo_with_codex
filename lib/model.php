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
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS todos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            is_completed INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $columns = $pdo->query('PRAGMA table_info(todos)')->fetchAll(PDO::FETCH_ASSOC);
    $hasCategoryId = array_filter($columns, static fn(array $column): bool => $column['name'] === 'category_id') !== [];
    if (!$hasCategoryId) {
        $pdo->exec('ALTER TABLE todos ADD COLUMN category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL');
    }

    return $pdo;
}

/** @return list<array{id: int, title: string, is_completed: int, category_id: ?int, category_name: ?string, created_at: string}> */
function findAllTodos(?int $categoryId = null, bool $onlyUncategorized = false): array
{
    $sql = 'SELECT todos.id, todos.title, todos.is_completed, todos.category_id, categories.name AS category_name, todos.created_at
            FROM todos LEFT JOIN categories ON categories.id = todos.category_id';
    if ($categoryId !== null) {
        $sql .= ' WHERE todos.category_id = :category_id';
    } elseif ($onlyUncategorized) {
        $sql .= ' WHERE todos.category_id IS NULL';
    }
    $sql .= ' ORDER BY todos.is_completed ASC, todos.id DESC';
    $statement = todoDatabase()->prepare($sql);
    $statement->execute($categoryId === null ? [] : [':category_id' => $categoryId]);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/** @return array{id: int, title: string, is_completed: int, category_id: ?int, category_name: ?string, created_at: string}|null */
function findTodo(int $id): ?array
{
    $statement = todoDatabase()->prepare(
        'SELECT todos.id, todos.title, todos.is_completed, todos.category_id, categories.name AS category_name, todos.created_at
         FROM todos LEFT JOIN categories ON categories.id = todos.category_id WHERE todos.id = :id'
    );
    $statement->execute([':id' => $id]);
    $todo = $statement->fetch(PDO::FETCH_ASSOC);
    return $todo === false ? null : $todo;
}

function createTodo(string $title, ?int $categoryId): void
{
    $statement = todoDatabase()->prepare('INSERT INTO todos (title, category_id) VALUES (:title, :category_id)');
    $statement->execute([':title' => $title, ':category_id' => $categoryId]);
}

function updateTodo(int $id, string $title, ?int $categoryId): void
{
    $statement = todoDatabase()->prepare('UPDATE todos SET title = :title, category_id = :category_id WHERE id = :id');
    $statement->execute([':id' => $id, ':title' => $title, ':category_id' => $categoryId]);
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
function findAllCategories(): array
{
    return todoDatabase()->query('SELECT id, name, created_at FROM categories ORDER BY name COLLATE NOCASE ASC')->fetchAll(PDO::FETCH_ASSOC);
}

/** @return array{id: int, name: string, created_at: string}|null */
function findCategory(int $id): ?array
{
    $statement = todoDatabase()->prepare('SELECT id, name, created_at FROM categories WHERE id = :id');
    $statement->execute([':id' => $id]);
    $category = $statement->fetch(PDO::FETCH_ASSOC);
    return $category === false ? null : $category;
}

function createCategory(string $name): void
{
    $statement = todoDatabase()->prepare('INSERT INTO categories (name) VALUES (:name)');
    $statement->execute([':name' => $name]);
}

function updateCategory(int $id, string $name): void
{
    $statement = todoDatabase()->prepare('UPDATE categories SET name = :name WHERE id = :id');
    $statement->execute([':id' => $id, ':name' => $name]);
}

function deleteCategory(int $id): void
{
    $statement = todoDatabase()->prepare('DELETE FROM categories WHERE id = :id');
    $statement->execute([':id' => $id]);
}

<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TodoFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        todoDatabase()->exec('DELETE FROM todos');
        todoDatabase()->exec('DELETE FROM categories');
    }

    public function testCreatesAndRetrievesATodo(): void
    {
        createCategory('仕事');
        $category = findAllCategories()[0];

        createTodo('見積書を作成する', (int) $category['id']);

        $todos = findAllTodos();

        self::assertCount(1, $todos);
        self::assertSame('見積書を作成する', $todos[0]['title']);
        self::assertSame('仕事', $todos[0]['category_name']);
        self::assertSame(0, (int) $todos[0]['is_completed']);
    }

    public function testUpdatesATodo(): void
    {
        createTodo('下書き', null);
        $todo = findAllTodos()[0];

        createCategory('個人');
        $category = findAllCategories()[0];
        updateTodo((int) $todo['id'], '提出用の原稿を仕上げる', (int) $category['id']);
        toggleTodo((int) $todo['id']);

        $updatedTodo = findTodo((int) $todo['id']);

        self::assertNotNull($updatedTodo);
        self::assertSame('提出用の原稿を仕上げる', $updatedTodo['title']);
        self::assertSame('個人', $updatedTodo['category_name']);
        self::assertSame(1, (int) $updatedTodo['is_completed']);
    }

    public function testDeletesATodo(): void
    {
        createTodo('削除するTODO', null);
        $todo = findAllTodos()[0];

        deleteTodo((int) $todo['id']);

        self::assertNull(findTodo((int) $todo['id']));
        self::assertSame([], findAllTodos());
    }
}

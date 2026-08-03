<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        todoDatabase()->exec('DELETE FROM todos');
        todoDatabase()->exec('DELETE FROM tags');
    }

    public function testCreatesTagsFromTodoInput(): void
    {
        $input = parseTodoInput('買い出しに行く #家事 #毎日');
        createTodo($input['title'], $input['tag_names']);

        $todo = findAllTodos()[0];

        self::assertSame('買い出しに行く', $todo['title']);
        self::assertSame('家事 • 毎日', $todo['tag_names']);
        self::assertCount(2, findAllTags());
    }

    public function testUpdatesTodoTagsFromTodoInput(): void
    {
        createTodo('下書き', ['仕事']);
        $todo = findAllTodos()[0];
        $input = parseTodoInput('提出する #重要 #毎日');

        updateTodo((int) $todo['id'], $input['title'], $input['tag_names']);

        $updatedTodo = findTodo((int) $todo['id']);
        self::assertSame('提出する', $updatedTodo['title']);
        self::assertCount(2, $updatedTodo['tag_ids']);
        self::assertSame('重要 • 毎日', $updatedTodo['tag_names']);
    }
}

<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TodoFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        todoDatabase()->exec('DELETE FROM todos');
        todoDatabase()->exec('DELETE FROM tags');
    }

    public function testCreatesAndRetrievesATodo(): void
    {
        createTodo('見積書を作成する', ['仕事']);

        $todos = findAllTodos();

        self::assertCount(1, $todos);
        self::assertSame('見積書を作成する', $todos[0]['title']);
        self::assertSame('仕事', $todos[0]['tag_names']);
        self::assertSame(0, (int) $todos[0]['is_completed']);
    }

    public function testUpdatesATodo(): void
    {
        createTodo('下書き', []);
        $todo = findAllTodos()[0];

        updateTodo((int) $todo['id'], '提出用の原稿を仕上げる', ['個人', '重要']);
        toggleTodo((int) $todo['id']);

        $updatedTodo = findTodo((int) $todo['id']);

        self::assertNotNull($updatedTodo);
        self::assertSame('提出用の原稿を仕上げる', $updatedTodo['title']);
        self::assertCount(2, $updatedTodo['tag_ids']);
        self::assertSame(1, (int) $updatedTodo['is_completed']);
    }

    public function testDeletesATodo(): void
    {
        createTodo('削除するTODO', []);
        $todo = findAllTodos()[0];

        deleteTodo((int) $todo['id']);

        self::assertNull(findTodo((int) $todo['id']));
        self::assertSame([], findAllTodos());
    }

    public function testSearchesTodosByTextStatusAndMultipleTags(): void
    {
        createTodo('牛乳を買い出しに行く', ['家事', '毎日']);
        createTodo('週次報告を提出する', ['仕事']);
        foreach (findAllTodos() as $todo) {
            if ($todo['title'] === '週次報告を提出する') {
                toggleTodo((int) $todo['id']);
            }
        }

        $matchingTodos = findAllTodos(null, false, '買い出し', 'incomplete', $this->findTagIdsByNames(['家事', '毎日']));

        self::assertCount(1, $matchingTodos);
        self::assertSame('牛乳を買い出しに行く', $matchingTodos[0]['title']);
        self::assertSame([], findAllTodos(null, false, '', 'completed', $this->findTagIdsByNames(['家事'])));
    }

    public function testPaginatesTodosAndCountsMatchingResults(): void
    {
        createTodo('ページ1', []);
        createTodo('ページ2', []);
        createTodo('ページ3', []);

        $firstPage = findAllTodos(null, false, '', 'all', [], 2, 0);
        $secondPage = findAllTodos(null, false, '', 'all', [], 2, 2);

        self::assertSame(3, countTodos());
        self::assertCount(2, $firstPage);
        self::assertCount(1, $secondPage);
    }

    /** @param list<string> $tagNames
     *  @return list<int> */
    private function findTagIdsByNames(array $tagNames): array
    {
        return array_map(
            static fn(string $tagName): int => (int) array_values(array_filter(
                findAllTags(),
                static fn(array $tag): bool => $tag['name'] === $tagName
            ))[0]['id'],
            $tagNames
        );
    }
}

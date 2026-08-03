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

    public function testCreatesAndRetrievesATag(): void
    {
        createTag('仕事');
        $tags = findAllTags();

        self::assertCount(1, $tags);
        self::assertSame('仕事', $tags[0]['name']);
        self::assertSame($tags[0], findTag((int) $tags[0]['id']));
    }

    public function testUpdatesATag(): void
    {
        createTag('仮タグ');
        $tag = findAllTags()[0];
        updateTag((int) $tag['id'], 'プライベート');

        self::assertSame('プライベート', findTag((int) $tag['id'])['name']);
    }

    public function testDeletesATagAndKeepsRelatedTodos(): void
    {
        createTag('削除するタグ');
        $tag = findAllTags()[0];
        createTodo('タグ付きのTODO', [(int) $tag['id']]);
        $todo = findAllTodos()[0];
        deleteTag((int) $tag['id']);

        self::assertNull(findTag((int) $tag['id']));
        self::assertSame([], findTodo((int) $todo['id'])['tag_ids']);
    }
}

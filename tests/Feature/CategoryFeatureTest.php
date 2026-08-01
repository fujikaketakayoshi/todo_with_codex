<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CategoryFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        todoDatabase()->exec('DELETE FROM todos');
        todoDatabase()->exec('DELETE FROM categories');
    }

    public function testCreatesAndRetrievesACategory(): void
    {
        createCategory('仕事');

        $categories = findAllCategories();

        self::assertCount(1, $categories);
        self::assertSame('仕事', $categories[0]['name']);
        self::assertSame($categories[0], findCategory((int) $categories[0]['id']));
    }

    public function testUpdatesACategory(): void
    {
        createCategory('仮カテゴリ');
        $category = findAllCategories()[0];

        updateCategory((int) $category['id'], 'プライベート');

        $updatedCategory = findCategory((int) $category['id']);

        self::assertNotNull($updatedCategory);
        self::assertSame('プライベート', $updatedCategory['name']);
    }

    public function testDeletesACategoryAndUnassignsRelatedTodos(): void
    {
        createCategory('削除するカテゴリ');
        $category = findAllCategories()[0];
        createTodo('カテゴリ付きのTODO', (int) $category['id']);
        $todo = findAllTodos()[0];

        deleteCategory((int) $category['id']);

        $updatedTodo = findTodo((int) $todo['id']);

        self::assertNull(findCategory((int) $category['id']));
        self::assertNotNull($updatedTodo);
        self::assertNull($updatedTodo['category_id']);
        self::assertNull($updatedTodo['category_name']);
    }
}

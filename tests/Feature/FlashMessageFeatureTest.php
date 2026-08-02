<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FlashMessageFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testPullsAFlashMessageOnlyOnce(): void
    {
        storeFlashMessage('TODOを追加しました。');

        self::assertSame(
            ['message' => 'TODOを追加しました。', 'type' => 'success'],
            pullFlashMessage()
        );
        self::assertNull(pullFlashMessage());
    }

    public function testStoresAnErrorFlashMessage(): void
    {
        storeFlashMessage('TODOは100文字以内で入力してください。', 'error');

        self::assertSame(
            ['message' => 'TODOは100文字以内で入力してください。', 'type' => 'error'],
            pullFlashMessage()
        );
    }
}

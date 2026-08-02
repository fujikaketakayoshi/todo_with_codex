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
        $_SESSION['flash_message'] = 'TODOを追加しました。';

        self::assertSame('TODOを追加しました。', pullFlashMessage());
        self::assertNull(pullFlashMessage());
    }
}

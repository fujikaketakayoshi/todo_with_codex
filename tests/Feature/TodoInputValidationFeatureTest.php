<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TodoInputValidationFeatureTest extends TestCase
{
    public function testAllowsTodoTitlesUpToOneHundredCharacters(): void
    {
        $title = str_repeat('あ', TODO_TITLE_MAXIMUM_LENGTH);

        self::assertSame(100, TODO_TITLE_MAXIMUM_LENGTH);
        self::assertTrue(isWithinMaximumLength($title, TODO_TITLE_MAXIMUM_LENGTH));
    }

    public function testRejectsTodoTitlesOverOneHundredCharacters(): void
    {
        $title = str_repeat('あ', TODO_TITLE_MAXIMUM_LENGTH + 1);

        self::assertFalse(isWithinMaximumLength($title, TODO_TITLE_MAXIMUM_LENGTH));
    }
}

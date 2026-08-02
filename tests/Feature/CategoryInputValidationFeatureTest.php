<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CategoryInputValidationFeatureTest extends TestCase
{
    public function testAllowsCategoryNamesUpToThirtyCharacters(): void
    {
        $name = str_repeat('あ', CATEGORY_NAME_MAXIMUM_LENGTH);

        self::assertSame(30, CATEGORY_NAME_MAXIMUM_LENGTH);
        self::assertTrue(isWithinMaximumLength($name, CATEGORY_NAME_MAXIMUM_LENGTH));
    }

    public function testRejectsCategoryNamesOverThirtyCharacters(): void
    {
        $name = str_repeat('あ', CATEGORY_NAME_MAXIMUM_LENGTH + 1);

        self::assertFalse(isWithinMaximumLength($name, CATEGORY_NAME_MAXIMUM_LENGTH));
    }
}

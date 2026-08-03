<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagInputValidationFeatureTest extends TestCase
{
    public function testAllowsTagNamesUpToThirtyCharacters(): void
    {
        self::assertTrue(isWithinMaximumLength(str_repeat('あ', TAG_NAME_MAXIMUM_LENGTH), TAG_NAME_MAXIMUM_LENGTH));
    }

    public function testRejectsTagNamesOverThirtyCharacters(): void
    {
        self::assertFalse(isWithinMaximumLength(str_repeat('あ', TAG_NAME_MAXIMUM_LENGTH + 1), TAG_NAME_MAXIMUM_LENGTH));
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Tests\Helper;

use PHPPress\Helper\Arr;
use PHPPress\Tests\Provider\ArrProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};

/**
 * Test case for the Arr class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('helpers')]
final class ArrTest extends \PHPUnit\Framework\TestCase
{
    #[DataProviderExternal(ArrProvider::class, 'isAssociative')]
    public function testIsAssociative(array $value, bool $expected): void
    {
        $this->assertSame($expected, Arr::isAssociative($value));
    }

    public function testIsAssociativeWithStricModeFalse(): void
    {
        $this->assertTrue(Arr::isAssociative(['name' => 1, 'value' => 'test', 3], false));
    }

    #[DataProviderExternal(ArrProvider::class, 'isList')]
    public function testIsList(array $value, bool $expected): void
    {
        $this->assertSame(Arr::isList($value), $expected);
    }
}

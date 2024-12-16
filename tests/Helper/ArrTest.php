<?php

declare(strict_types=1);

namespace PHPPress\Tests\Helper;

use PHPPress\Helper\Arr;
use PHPPress\Tests\Provider\ArrProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};

/**
 * Test case for the {@see Arr} class.
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

    #[DataProviderExternal(ArrProvider::class, 'isList')]
    public function testIsList(array $value, bool $expected): void
    {
        $this->assertSame(Arr::isList($value), $expected);
    }
}

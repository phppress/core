<?php

declare(strict_types=1);

namespace PHPPress\Tests\Helper;

use PHPPress\Helper\Str;
use PHPPress\Tests\Provider\StrProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};

/**
 * Test case for the {@see Str} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('helpers')]
final class StrTest extends \PHPUnit\Framework\TestCase
{
    #[DataProviderExternal(StrProvider::class, 'matchWildcard')]
    public function testMatchWildcard(
        string $pattern,
        string $string,
        bool $expected,
        false|null $caseSensitive = null,
    ): void {
        match ($caseSensitive) {
            false => $this->assertSame($expected, Str::matchWildcard($pattern, $string, false)),
            default => $this->assertSame($expected, Str::matchWildcard($pattern, $string)),
        };
    }

    #[DataProviderExternal(StrProvider::class, 'matchWildcardUsingEscapeFalseValue')]
    public function testMatchWildcardUsingEscapeFalseValue(
        string $pattern,
        string $string,
        bool $expected,
    ): void {
        $this->assertSame($expected, Str::matchWildcard($pattern, $string, escape: false));
    }

    #[DataProviderExternal(StrProvider::class, 'matchWildcardUsingFilePathTrueValue')]
    public function testMatchWildcardUsingCaseFilePathTrueValue(string $pattern, string $string, bool $expected): void
    {
        $this->assertSame($expected, Str::matchWildcard($pattern, $string, filePath: true));
    }

    #[DataProviderExternal(StrProvider::class, 'matchWildcardUsingFilePathTrueValueAndEscapeFalseValue')]
    public function testMatchWildcardUsingFilePathTrueValueAndEscapeFalseValue(
        string $pattern,
        string $string,
        bool $expected,
    ): void {
        $this->assertSame($expected, Str::matchWildcard($pattern, $string, filePath: true, escape: false));
    }
}

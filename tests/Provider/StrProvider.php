<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

/**
 * Provider for the Arr class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class StrProvider
{
    /**
     * Data provider for the matchWildcard method.
     *
     * @phpstan-return array<array{array<array-key, int|string>, bool}>
     */
    public static function matchWildcard(): array
    {
        return [
            // *
            ['*', 'any', true],
            ['*', '', true],
            ['begin*end', 'begin-middle-end', true],
            ['begin*end', 'beginend', true],
            ['begin*end', 'begin-d', false],
            ['*end', 'beginend', true],
            ['*end', 'begin', false],
            ['begin*', 'begin-end', true],
            ['begin*', 'end', false],
            ['begin*', 'before-begin', false],
            // ?
            ['begin?end', 'begin1end', true],
            ['begin?end', 'beginend', false],
            ['begin??end', 'begin12end', true],
            ['begin??end', 'begin1end', false],
            // []
            ['gr[ae]y', 'gray', true],
            ['gr[ae]y', 'grey', true],
            ['gr[ae]y', 'groy', false],
            ['a[2-8]', 'a1', false],
            ['a[2-8]', 'a3', true],
            ['[][!]', ']', true],
            ['[-1]', '-', true],
            // [!]
            ['gr[!ae]y', 'gray', false],
            ['gr[!ae]y', 'grey', false],
            ['gr[!ae]y', 'groy', true],
            ['a[!2-8]', 'a1', true],
            ['a[!2-8]', 'a3', false],
            // -
            ['a-z', 'a-z', true],
            ['a-z', 'a-c', false],
            // case
            ['Hello*', 'helloWorld', false],
            ['Hello*', 'helloWorld', true, false],
            // slashes
            ['begin/*/end', 'begin/middle/end', true],
            ['begin/*/end', 'begin/two/steps/end', true],
            ['begin/*/end', 'begin/end', false],
            ['begin\\\\*\\\\end', 'begin\middle\end', true],
            ['begin\\\\*\\\\end', 'begin\two\steps\end', true],
            ['begin\\\\*\\\\end', 'begin\end', false],
            // dots
            ['begin.*.end', 'begin.middle.end', true],
            ['begin.*.end', 'begin.two.steps.end', true],
            ['begin.*.end', 'begin.end', false],
            // escaping
            ['\*\?', '*?', true],
            ['\*\?', 'zz', false],
        ];
    }

    /**
     * Data provider for the matchWildcard method using `caseSensitive` option `true` and `escape` option `false` and
     * `filePath` option `false`.
     *
     * @phpstan-return array<array{array<array-key, int|string>, bool}>
     */
    public static function matchWildcardUsingEscapeFalseValue(): array
    {
        return [
            ['begin\*\end', 'begin\middle\end', true],
            ['begin\*\end', 'begin\two\steps\end', true],
            ['begin\*\end', 'begin\end', false],
        ];
    }

    /**
     * Data provider for the matchWildcard method using `caseSensitive` option `true`, `escape` option `true` and
     * `filePath` option `true`.
     *
     * @phpstan-return array<array{array<array-key, int|string>, bool}>
     */
    public static function matchWildcardUsingFilePathTrueValue(): array
    {
        return [
            ['begin/*/end', 'begin/middle/end', true],
            ['begin/*/end', 'begin/two/steps/end', false],
            ['begin\\\\*\\\\end', 'begin\middle\end', true],
            ['begin\\\\*\\\\end', 'begin\two\steps\end', false],
            ['*', 'any', true],
            ['*', 'any/path', false],
            ['[.-0]', 'any/path', false],
            ['*', '.dotenv', true],
        ];
    }

    /**
     * Data provider for the matchWildcard method using `caseSensitive` option `true`, `escape` option `false` and
     * `filePath` option `true`.
     *
     * @phpstan-return array<array{array<array-key, int|string>, bool}>
     */
    public static function matchWildcardUsingFilePathTrueValueAndEscapeFalseValue(): array
    {
        return [
            ['begin\*\end', 'begin\middle\end', true],
            ['begin\*\end', 'begin\two\steps\end', false],
        ];
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Helper;

use function preg_match;
use function preg_quote;
use function strtr;

/**
 * Provides concrete implementation for {@see Str::class}.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
abstract class AbstractString
{
    private const array DEFAULT_REPLACEMENT = [
        '\\\\\\\\' => '\\\\',
        '\\\\\\*' => '[*]',
        '\\\\\\?' => '[?]',
        '\*' => '.*',
        '\?' => '.',
        '\[\!' => '[^',
        '\[' => '[',
        '\]' => ']',
        '\-' => '-',
    ];

    /**
     * Determines if a given string matches the specified shell wildcard pattern.
     *
     * This method emulates the functionality of {@see fnmatch()}, which may not be available in certain environments,
     * by using a PCRE-based implementation.
     *
     * @param string $pattern The shell wildcard pattern to be matched.
     * @param string $string The input string to test against the pattern.
     * @param bool $caseSensitive Whether the matching should be case-sensitive. Defaults to `true`.
     * @param bool $escape Whether backslash escaping is enabled in the pattern. Defaults to `true`.
     * @param bool $filePath Whether slashes in the string should only match slashes in the pattern.
     * Defaults to `false`.
     *
     * @return bool Returns `true` if the string matches the specified pattern, or `false` otherwise.
     */
    public static function matchWildcard(
        string $pattern,
        string $string,
        bool $caseSensitive = true,
        bool $escape = true,
        bool $filePath = false,
    ): bool {
        return self::doMatch($pattern, $string, $caseSensitive, $escape, $filePath);
    }

    /**
     * Performs the actual wildcard pattern matching by converting wildcards to regular expressions.
     *
     * @param string $pattern The wildcard pattern to be converted to regex.
     * @param string $string The string to match against the pattern.
     * @param bool $caseSensitive Whether to perform case-sensitive matching.
     * @param bool $escape Whether to support escape sequences with backslash.
     * @param bool $filePath Whether to match file paths.
     *
     * @return bool `true` if the string matches the pattern, `false` otherwise.
     */
    private static function doMatch(
        string $pattern,
        string $string,
        bool $caseSensitive,
        bool $escape,
        bool $filePath,
    ): bool {
        if ($pattern === '*' && $filePath === false) {
            return true;
        }

        $replacements = self::DEFAULT_REPLACEMENT;

        if ($escape === false) {
            unset($replacements['\\\\\\\\'], $replacements['\\\\\\*'], $replacements['\\\\\\?']);
        }

        if ($filePath === true) {
            $replacements['\*'] = '[^/\\\\]*';
            $replacements['\?'] = '[^/\\\\]';
        }

        $pattern = strtr(preg_quote($pattern, '#'), $replacements);
        $pattern = "#^{$pattern}$#us";

        if ($caseSensitive === false) {
            $pattern .= 'i';
        }

        return preg_match($pattern, $string) === 1;
    }
}

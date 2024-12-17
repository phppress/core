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
     * Checks if the passed string would match the given shell wildcard pattern.
     * This function emulates {@see fnmatch()}, which may be unavailable at certain environment, using PCRE.
     *
     * @param string $pattern the shell wildcard pattern.
     * @param string $string the tested string.
     * @param bool $caseSensitive whether pattern should be case sensitive. Defaults to `true`.
     * @param bool $escape whether backslash escaping is enabled. Defaults to `true`.
     * @param bool $filePath whether slashes in string only matches slashes in the given pattern. Defaults to `false`.
     *
     * @return bool `true` if the string matches the pattern, `false` otherwise.
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
            unset($replacements['\\\\\\\\']);
            unset($replacements['\\\\\\*']);
            unset($replacements['\\\\\\?']);
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

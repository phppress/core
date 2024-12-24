<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

/**
 * Provider for the {@see \PHPPress\Tests\Helper\MiddlewareDispatcherTest} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class MiddlewareDispatcherProvider
{
    /**
     * Data provider for the matchesPathPrefix method.
     *
     * @phpstan-return array<array{string, string}>
     */
    public static function matchesPathPrefix(): array
    {
        return [
            'both-missing-slash-should-match' => ['api', 'api/users', true],
            'double-slashes-should-normalize' => ['//api//v1//', '//api//v1//users', true],
            'empty-both-slashes' => ['', '/foo/'],
            'empty-leading-slash' => ['', '/foo'],
            'empty-nested-path' => ['', '/foo/bar'],
            'empty-not-slash' => ['', 'foo'],
            'empty-prefix-special-case' => ['', '/any/path', true],
            'empty-trailing-slash' => ['', 'foo/'],
            'missing-slash-should-match' => ['api', '/api/users', true],
            'nested-path-one-nested-path' => ['foo/bar/', '/foo/bar'],
            'nested-path-two-nested-path' => ['foo/bar', '/foo/bar/baz/'],
            'nested-path-without-leading-slash' => ['api/v1', '/api/v1/users', true],
            'path-both-slashes' => ['foo/', '/foo/'],
            'path-leading-slash' => ['foo', '/foo'],
            'path-nested-path-file' => ['foo', '/foo/bar/file.txt'],
            'path-not-slash' => ['/foo', 'foo'],
            'path-one-nested-path' => ['foo', '/foo/bar'],
            'path-trailing-slash' => ['/foo/', 'foo/'],
            'path-two-nested-path' => ['foo', '/foo/bar/baz/'],
            'prefix-with-slash-request-without-should-match' => ['/api', 'api/users', true],
            'root-path-special-case' => ['/', '/any/path', true],
            'slash-both-slashes' => ['/', '/foo/'],
            'slash-leading-slash' => ['/', '/foo'],
            'slash-nested-path' => ['/', '/foo/bar'],
            'slash-not-slash' => ['/', 'foo'],
            'slash-trailing-slash' => ['/', 'foo/'],
            'verify-leading-slash-normalization' => ['api/test', 'api/test/something', true],
            'verify-leading-slash-exact-match' => ['api', 'api', true],
        ];
    }

    /**
     * Data provider for the notMatchesPathPrefix method.
     *
     * @phpstan-return array<array{string, string}>
     */
    public static function notMatchesPathPrefix(): array
    {
        return [
            'case-sensitive-no-match' => ['API', '/api/users', false],
            'different-nesting-no-match' => ['api/v1', '/api/v2', false],
            'not-equal-both-slashes' => ['/foo/', '/bar/'],
            'not-equal-leading-slash' => ['/foo', '/bar'],
            'not-equal-nested' => ['/foo/bar', '/foo/baz'],
            'not-equal-not-slash' => ['foo', 'bar'],
            'not-equal-one-nested' => ['/foo/bar/', '/foo'],
            'not-equal-path-boundaries' => ['/foo', '/foobar'],
            'not-equal-trailing-slash' => ['foo/', 'bar/'],
            'not-equal-two-nested' => ['/foo/bar/baz', '/foo/bar/'],
            'partial-prefix-no-match' => ['api', '/api2/users', false],
        ];
    }
}

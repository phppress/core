<?php

declare(strict_types=1);

namespace PHPPress\Tests\Http\Emitter\Stub;

use function array_key_exists;
use function explode;
use function strtolower;

/**
 * Mocks for HTTP functions.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class HTTPFunctions
{
    /**
     * @var string[][]
     */
    private static array $headers = [];
    private static int $responseCode = 200;
    private static bool $headersSent = false;
    private static string $headersSentFile = '';
    private static int $headersSentLine = 0;
    private static string $rawHttpHeader = '';
    private static int $flushedTimes = 0;

    /**
     * Reset state
     */
    public static function reset(): void
    {
        self::$headers = [];
        self::$responseCode = 200;
        self::$headersSent = false;
        self::$headersSentFile = '';
        self::$headersSentLine = 0;
        self::$rawHttpHeader = '';
        self::$flushedTimes = 0;
    }

    public static function set_headers_sent(bool $value = false, string $file = '', int $line = 0): void
    {
        self::$headersSent = $value;
        self::$headersSentFile = $file;
        self::$headersSentLine = $line;
    }

    public static function headers_sent(&$file = null, &$line = null): bool
    {
        $file = self::$headersSentFile;
        $line = self::$headersSentLine;
        return self::$headersSent;
    }

    public static function header(string $string, bool $replace = true, ?int $http_response_code = 0): void
    {
        if (!str_starts_with($string, 'HTTP/')) {
            $header = strtolower(explode(':', $string, 2)[0]);
            if ($replace || !array_key_exists($header, self::$headers)) {
                self::$headers[$header] = [];
            }
            self::$headers[$header][] = $string;
        } else {
            self::$rawHttpHeader = $string;
        }
        if ($http_response_code !== 0) {
            self::$responseCode = $http_response_code;
        }
    }

    public static function header_remove(?string $header = null): void
    {
        if ($header === null) {
            self::$headers = [];
        } else {
            unset(self::$headers[strtolower($header)]);
        }
    }

    /**
     * @return string[]
     */
    public static function headers_list(): array
    {
        $result = [];
        foreach (self::$headers as $values) {
            foreach ($values as $header) {
                $result[] = $header;
            }
        }
        return $result;
    }

    public static function http_response_code(?int $response_code = 0): int
    {
        if ($response_code !== 0) {
            self::$responseCode = $response_code;
        }
        return self::$responseCode;
    }

    public static function hasHeader(string $header): bool
    {
        return array_key_exists(strtolower($header), self::$headers);
    }

    public static function rawHttpHeader(): string
    {
        return self::$rawHttpHeader;
    }

    public static function getHeader(string $header): array
    {
        return self::$headers[strtolower($header)] ?? [];
    }

    public static function flush(): void
    {
        self::$flushedTimes++;
    }

    public static function getFlushTimes(): int
    {
        return self::$flushedTimes;
    }
}

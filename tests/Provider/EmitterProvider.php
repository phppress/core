<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

use PHPPress\Http\Emitter\HttpNoBodyStatus;

/**
 * Provider for the {@see Emitter} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class EmitterProvider
{
    public static function body(): array
    {
        return [
            ['', [''],  null, null, null],
            ['Contents', ['Contents'], null, null, null],
            ['Contents', ['Contents'], 8192, null, null],
            ['Contents', ['C', 'o', 'n', 't', 'e', 'n', 't', 's'], 1, null, null],
            ['Contents', ['Co', 'nt', 'en', 'ts'], 2, null, null],
            ['Contents', ['Con', 'ten', 'ts'], 3, null, null],
            ['Contents', ['Content', 's'], 7, null, null],
            ['Contents', ['Contents'], 8192, 0, 8],
            ['Contents', ['Con'], 8192, 0, 2],
            ['Contents', ['Content', 's'], 7, 0, 7],
            ['Contents', ['C', 'o', 'n', 't', 'e', 'n', 't', 's'], 1, 0, 8],
            ['Contents', ['Co', 'nt'], 2, 0, 3],
            ['Contents', ['nte', 'nt'], 3, 2, 6],
            ['Contents', ['ts'], 2, 6, 8],
        ];
    }

    public static function noBodyStatusCodes(): array
    {
        return array_map(
            static fn(HttpNoBodyStatus $status): array => [
                'code' => $status->value,
                'phrase' => match ($status) {
                    HttpNoBodyStatus::CONTINUE => 'Continue',
                    HttpNoBodyStatus::SWITCHING_PROTOCOLS => 'Switching Protocols',
                    HttpNoBodyStatus::PROCESSING => 'Processing',
                    HttpNoBodyStatus::EARLY_HINTS => 'Early Hints',
                    HttpNoBodyStatus::NO_CONTENT => 'No Content',
                    HttpNoBodyStatus::RESET_CONTENT => 'Reset Content',
                    HttpNoBodyStatus::NOT_MODIFIED => 'Not Modified',
                },
            ],
            HttpNoBodyStatus::cases(),
        );
    }

    public static function reasonPhrase(): array
    {
        return [
            'empty_reason_phrase' => [
                599,
                '',
                'HTTP/1.1 599',
            ],
            'standard_404' => [
                404,
                'Not Found',
                'HTTP/1.1 404 Not Found',
            ],
            'standard_200' => [
                200,
                'OK',
                'HTTP/1.1 200 OK',
            ],
            'custom_reason_phrase' => [
                599,
                'I\'m a teapot',
                'HTTP/1.1 599 I\'m a teapot',
            ],
            'whitespace_reason_phrase' => [
                599,
                ' ',
                'HTTP/1.1 599  ',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

use Iterator;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokableBuiltInPHPClassOptional
{
    public function __invoke(Iterator|null $iterator = null): array
    {
        return [
            'iterator' => $iterator,
        ];
    }
}

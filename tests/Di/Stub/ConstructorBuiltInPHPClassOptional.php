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
final class ConstructorBuiltInPHPClassOptional
{
    private Iterator|null $iterator;

    public function __construct(Iterator|null $iterator = null)
    {
        $this->iterator = $iterator;
    }

    public function getConstructorArguments(): array
    {
        return [
            'iterator' => $this->iterator,
        ];
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

use DateTime;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokeableBuiltInPHPClass
{
    public function __invoke(DateTime $dateTime): DateTime
    {
        return $dateTime;
    }
}

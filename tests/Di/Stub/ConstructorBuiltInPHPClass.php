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
final class ConstructorBuiltInPHPClass
{
    private DateTime $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function getFormattedDate(string $format = 'Y-m-d'): string
    {
        return $this->dateTime->format($format);
    }
}

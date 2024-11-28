<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class EngineMarkOneInmutable
{
    public const NAME = 'Mark One';

    public function __construct(private int $number = 1)
    {
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function withNumber(int $value): self
    {
        $new = clone $this;
        $new->number = $value;

        return $new;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}

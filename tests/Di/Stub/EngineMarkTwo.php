<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class EngineMarkTwo implements EngineInterface, EngineColorInterface
{
    private string $color = 'red';
    public const string NAME = 'Mark Two';
    public const int NUMBER = 2;

    public function __construct(private int $number = self::NUMBER) {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function setNumber(int $value): void
    {
        $this->number = $value;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $value): void
    {
        $this->color = $value;
    }
}

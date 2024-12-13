<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class Instance implements InstanceInterface
{
    public int $c = 0;
    public int $d = 0;
    private int $a = 0;
    private int $b = 0;

    public function getA(): int
    {
        return $this->a;
    }

    public function getB(): int
    {
        return $this->b;
    }

    public function setA(int $a): void
    {
        $this->a = $a;
    }

    public function setB(int $b): void
    {
        $this->b = $b;
    }

    public function withD(int $d): self
    {
        $new = clone $this;
        $new->d = $d;

        return $new;
    }
}

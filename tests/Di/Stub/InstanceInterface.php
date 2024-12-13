<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
interface InstanceInterface
{
    public function getA(): int;
    public function getB(): int;
    public function setA(int $a): void;
    public function setB(int $b): void;
}

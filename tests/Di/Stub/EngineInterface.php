<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
interface EngineInterface
{
    public function getName(): string;
    public function setNumber(int $value): void;
    public function getNumber(): int;
}

<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorSeveralArguments
{
    public $callable;

    public function __construct(public array $array, callable $callable, public object $object, public string $string)
    {
        $this->callable = $callable;
    }
}

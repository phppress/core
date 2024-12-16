<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorCompoundType
{
    public $callable;

    public function __construct(public array $array, callable|null $callable, public object $object)
    {
        $this->callable = $callable;
    }

    public function getConstructorArguments(): array
    {
        return [
            'array' => $this->array,
            'callable' => $this->callable,
            'object' => $this->object,
        ];
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InstanceMethodSeveralArguments
{
    private array $array;
    private array $arrayCommon;
    private bool $boolean;
    private $callable;
    private float $float;
    private int $integer;
    private iterable $iterable;
    private object $object;
    private object $objectCommon;
    private string $string;

    public function compoundTypes(array $array, callable $callable, iterable $iterable, object $object): void
    {
        $this->array = $array;
        $this->callable = $callable;
        $this->iterable = $iterable;
        $this->object = $object;
    }

    public function commonArguments(array $array, object $object): void
    {
        $this->arrayCommon = $array;
        $this->objectCommon = $object;
    }

    public function scalarTypes(bool $boolean, float $float, int $integer, string $string): void
    {
        $this->boolean = $boolean;
        $this->float = $float;
        $this->integer = $integer;
        $this->string = $string;
    }

    public function getArguments(): array
    {
        return [
            'compoundTypes' => [
                'array' => $this->array,
                'callable' => $this->callable,
                'iterable' => $this->iterable,
                'object' => $this->object,
            ],
            'scalarTypes' => [
                'boolean' => $this->boolean,
                'float' => $this->float,
                'integer' => $this->integer,
                'string' => $this->string,
            ],
        ];
    }

    public function getCommonArguments(): array
    {
        return [
            'common' => [
                'array' => $this->arrayCommon,
                'object' => $this->objectCommon,
            ],
        ];
    }
}

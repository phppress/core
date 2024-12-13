<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InstanceMethodVariadic
{
    private array $array;
    private array $variadic;

    public function variadic(EngineInterface ...$variadic): void
    {
        $this->variadic = $variadic;
    }

    public function variadicCompundTypes(array $array, EngineColorInterface|null ...$variadic): void
    {
        $this->array = $array;
        $this->variadic = $variadic;
    }

    public function variadicOptional(array $array = [1, 2, 3], EngineInterface ...$variadic): void
    {
        $this->array = $array;
        $this->variadic = $variadic;
    }

    public function variadicScalarTypes(array $array, bool|int|float|string ...$variadic): void
    {
        $this->array = $array;
        $this->variadic = $variadic;
    }

    public function getVariadic(): array
    {
        return [
            'variadic' => array_map(static fn(EngineInterface $engine): string => $engine->getName(), $this->variadic),
        ];
    }

    public function getVariadicOptional(): array
    {
        return [
            'optional' => [
                'array' => $this->array,
                'variadic' => array_map(static fn(EngineInterface $engine): string => $engine->getName(), $this->variadic),
            ],
        ];
    }

    public function getVariadicCompundTypes(): array
    {
        return [
            'compundTypes' => [
                'array' => $this->array,
                'variadic' => array_map(
                    static fn(EngineColorInterface|null $engine): string|null => $engine?->getColor(),
                    $this->variadic,
                ),
            ],
        ];
    }

    public function getVariadicScalarTypes(): array
    {
        return [
            'scalarTypes' => [
                'array' => $this->array,
                'variadic' => $this->variadic,
            ],
        ];
    }
}

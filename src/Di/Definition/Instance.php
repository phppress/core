<?php

declare(strict_types=1);

namespace PHPPress\Di\Definition;

use PHPPress\Exception\InvalidArgument;

/**
 * Instance represents a reference to a named object in a dependency injection (DI) container.
 *
 * Instance is mainly used in two places:
 *
 * - When configuring a dependency injection container, you use Instance to reference a class name, interface name or
 *   alias name. The reference can later be resolved into the actual object by the container.
 *
 * The following example shows how to configure a DI container with Instance:
 *
 * ```php
 * $container = new Container(
 *     [
 *         'engine-one' => Stub\EngineMarkOne::class,
 *         'engine-two' => Stub\EngineMarkTwo::class,
 *         'instance' => [
 *             '__class' => Stub\ConstructorVariadic::class,
 *             '__construct()' => [
 *                 Instance::of('engine-one'),
 *                 Instance::of('engine-two'),
 *             ],
 *         ],
 *     ],
 * );
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class Instance
{
    /**
     * @throws InvalidArgument if `$id` is an empty string.
     */
    final public function __construct(public string $id)
    {
        if ($id === '') {
            throw new InvalidArgument('The required component "id" is empty.');
        }
    }

    /**
     * Creates a new Instance object.
     *
     * @param string $id The component ID.
     *
     * @throws InvalidArgument if `$id` is an empty string.
     *
     * @return static the new Instance object.
     */
    public static function of(string $id): static
    {
        return new static($id);
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Di;

use PHPPress\Di\Exception\Message;
use PHPPress\Exception\InvalidArgument;

/**
 * Instance represents a reference to a named object in a dependency injection (DI) container or a service locator.
 *
 * You may use [[get()]] to get the actual object referenced by [[id]].
 *
 * Instance is mainly used in two places:
 *
 * - when configuring a dependency injection container, you use Instance to reference a class name, interface name or
 *   alias name. The reference can later be resolved into the actual object by the container.
 * - in classes which use service locator to get dependent objects.
 *
 * The following example shows how to configure a DI container with Instance:
 *
 * ```php
 * ```
 *
 * And the following example shows how a class retrieves a component from a service locator:
 *
 * ```php
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
    final public function __construct(public readonly string $id)
    {
        if ($id === '') {
            throw new InvalidArgument(Message::COMPONENT_ID_EMPTY->getMessage());
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

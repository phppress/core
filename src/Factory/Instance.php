<?php

declare(strict_types=1);

namespace PHPPress\Factory;

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
            throw new InvalidArgument(Exception\Message::COMPONENT_ID_EMPTY->getMessage());
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

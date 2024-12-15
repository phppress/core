<?php

declare(strict_types=1);

namespace PHPPress\Di;

use PHPPress\Exception\InvalidConfig;

/**
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ObjectFactory
{
    public function __construct(private readonly ReflectionFactory $reflectionFactory)
    {
    }

    /**
     * Creates an object from the given definition.
     *
     * @param array|callable|string $object The object definition or class name.
     * @param array $definitions Additional definitions for dependencies.
     *
     * @throws InvalidConfig When the definition is empty or missing class option.
     *
     * @return mixed The created object instance.
     */
    public function create(array|callable|string $object, array $definitions = []): mixed
    {
        if ($object === '' || $object === []) {
            throw new InvalidConfig(Exception\Message::DEFINITION_EMPTY->getMessage());
        }

        if (is_string($object)) {
            return $this->reflectionFactory->create($object, $definitions);
        }

        if (is_callable($object, true)) {
            return $this->reflectionFactory->invoke($object, $definitions);
        }

        $class = $definitions['class'] ?? $definitions['__class'] ?? null;

        if ($class === null) {
            throw new InvalidConfig(Exception\Message::DEFINITION_REQUIRES_CLASS_OPTION->getMessage());
        }

        unset($definitions['__class'], $definitions['class']);

        return $this->reflectionFactory->create($class, $definitions);
    }
}

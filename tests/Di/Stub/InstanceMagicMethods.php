<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InstanceMagicMethods
{
    public string $name = 'default';

    public function __clone()
    {
        $this->name = 'cloned';
    }

    public function __debugInfo(): array
    {
        return ['name' => $this->name];
    }

    public function __destruct()
    {
        $this->name = 'destructed';
    }

    public function __get(string $name): string
    {
        return $name;
    }

    public function __isset(string $name): bool
    {
        return isset($this->$name);
    }

    public function __set(string $name, $value): void
    {
        $this->$name = $value;
    }

    public function __sleep(): array
    {
        return ['name'];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function __unset(string $name): void
    {
        unset($this->$name);
    }

    public function __wakeup(): void
    {
        $this->name = 'waked';
    }
}

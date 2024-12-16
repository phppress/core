<?php

declare(strict_types=1);

namespace PHPPress\Tests\Helper\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class PropertyObject extends MagicObject
{
    public string $content;
    private array $items = [];
    private object|null $object = null;
    private string|null $text = 'default';

    public function getText(): string|null
    {
        return $this->text;
    }

    public function setText($value): void
    {
        $this->text = $value;
    }

    public function getObject(): object
    {
        if ($this->object === null) {
            $this->object = new self();
            $this->object->text = 'object text';
        }

        return $this->object;
    }

    public function getExecute(): callable
    {
        return static function ($param) {
            return $param * 2;
        };
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setWriteOnly(): void {}
}

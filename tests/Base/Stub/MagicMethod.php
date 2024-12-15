<?php

declare(strict_types=1);

namespace PHPPress\Tests\Base\Stub;

final class MagicMethod
{
    use \PHPPress\Base\MagicMethod;

    private object|null $object = null;
    private string|null $text = 'default';
    private array $items = [];
    public string $content = '';

    public function getExecute(): callable
    {
        return static function (int $param): int {
            return $param * 2;
        };
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getObject(): object|null
    {
        if ($this->object === null) {
            $this->_object = new self();
            $this->object->text = 'object text';
        }

        return $this->object;
    }

    public function getText(): string|null
    {
        return $this->text;
    }

    public function setText(string|null $value): void
    {
        $this->text = $value;
    }

    public function setWriteOnly(): void
    {
    }
}

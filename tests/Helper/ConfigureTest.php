<?php

declare(strict_types=1);

namespace PHPPress\Tests\Helper;

use PHPPress\Helper\Configure;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the Configure class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('helpers')]
final class ConfigureTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigureObject(): void
    {
        $object = new class {
            public string $property = 'value';
            public int $immutableProperty = 1;

            public function setMethod(string $param1, string $param2): void
            {
                $this->property = $param1 . $param2;
            }

            public function withImmutableProperty(int $value): self
            {
                $clone = clone $this;
                $clone->immutableProperty = $value;

                return $clone;
            }
        };

        $this->assertSame('value', $object->property);
        $this->assertSame(1, $object->immutableProperty);

        $objectConfigure = Configure::object(
            $object,
            [
                'property' => 'value',
                'setMethod()' => ['param1', 'param2'],
                'withImmutableProperty()' => [2],
            ]
        );

        $this->assertSame('param1param2', $objectConfigure->property);
        $this->assertSame(2, $objectConfigure->immutableProperty);
        $this->assertNotSame($object, $objectConfigure);
        $this->assertNotSame($object->immutableProperty, $objectConfigure->immutableProperty);
    }
}

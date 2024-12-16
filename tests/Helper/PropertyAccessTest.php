<?php

declare(strict_types=1);

namespace PHPPress\Tests\Helper;

use PHPPress\Exception\{InvalidCall, UnknownProperty};
use PHPPress\Helper\PropertyAccess;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * Test case for the {@see PropertyAccess} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('helpers')]
final class PropertyAccessTest extends \PHPUnit\Framework\TestCase
{
    public function testFailsForGetWriteOnlyProperty(): void
    {
        $object = $this->createObject();

        $this->expectException(InvalidCall::class);
        $this->expectExceptionMessage(
            'Invalid call: "Getting write-only property: PHPPress\Tests\Helper\Stub\PropertyObject::writeOnly."',
        );

        PropertyAccess::get($object, 'writeOnly');
    }

    public function testFailsForGetUnknownProperty(): void
    {
        $object = $this->createObject();

        $this->expectException(UnknownProperty::class);
        $this->expectExceptionMessage(
            'Unknown property: "Getting unknown property: PHPPress\Tests\Helper\Stub\PropertyObject::unknown."',
        );

        PropertyAccess::get($object, 'unknown');
    }

    public function testFailsForSetReadOnlyProperty(): void
    {
        $object = $this->createObject();

        $this->expectException(InvalidCall::class);
        $this->expectExceptionMessage(
            'Invalid call: "Setting read-only property: PHPPress\Tests\Helper\Stub\PropertyObject::object."',
        );

        PropertyAccess::set($object, 'object', new stdClass());
    }

    public function testFailsForSetUnknownProperty(): void
    {
        $object = $this->createObject();

        $this->expectException(UnknownProperty::class);
        $this->expectExceptionMessage(
            'Unknown property: "Setting unknown property: PHPPress\Tests\Helper\Stub\PropertyObject::unknown."',
        );

        PropertyAccess::set($object, 'unknown', 'value');
    }

    public function testGet(): void
    {
        $object = $this->createObject();
        $object->setText('text');

        $this->assertSame('text', PropertyAccess::get($object, 'text'));
    }

    public function testHasMethod(): void
    {
        $object = $this->createObject();

        $this->assertFalse(PropertyAccess::hasMethod($object, 'unknown'));
        $this->assertTrue(PropertyAccess::hasMethod($object, 'getText'));
        $this->assertTrue(PropertyAccess::hasMethod($object, 'setText'));
        $this->assertTrue(PropertyAccess::hasMethod($object, 'getObject'));
        $this->assertTrue(PropertyAccess::hasMethod($object, 'getExecute'));
        $this->assertTrue(PropertyAccess::hasMethod($object, 'getItems'));
        $this->assertTrue(PropertyAccess::hasMethod($object, 'setWriteOnly'));
    }

    public function testIsRedeable(): void
    {
        $object = $this->createObject();

        $this->assertFalse(PropertyAccess::isReadable($object, 'execute'));
        $this->assertTrue(PropertyAccess::isReadable($object, 'items'));
        $this->assertTrue(PropertyAccess::isReadable($object, 'object'));
        $this->assertTrue(PropertyAccess::isReadable($object, 'text'));
    }

    public function testIsRedeabeUsingStrictMode(): void
    {
        $object = $this->createObject();

        $this->assertTrue(PropertyAccess::isReadable($object, 'execute', false));
    }

    public function testIsset(): void
    {
        $object = $this->createObject();

        $object->setText('text');

        $this->assertFalse(PropertyAccess::isset($object, 'unknown'));
        $this->assertTrue(PropertyAccess::isset($object, 'text'));
    }

    public function testIsSubclassOf(): void
    {
        $this->assertTrue(PropertyAccess::isSubclassOf(Stub\PropertyObject::class, Stub\MagicObject::class));
    }

    public function testIsSubclassOfUsingObject(): void
    {
        $this->assertTrue(PropertyAccess::isSubclassOf($this->createObject(), Stub\MagicObject::class, false));
    }

    public function testIsWritable(): void
    {
        $object = $this->createObject();

        $this->assertFalse(PropertyAccess::isWritable($object, 'items'));
        $this->assertFalse(PropertyAccess::isWritable($object, 'object'));
        $this->assertFalse(PropertyAccess::isWritable($object, 'writeOnly'));
        $this->assertTrue(PropertyAccess::isWritable($object, 'text'));
    }

    public function testIsWritableUsingStricMode(): void
    {
        $object = $this->createObject();

        $this->assertTrue(PropertyAccess::isWritable($object, 'writeOnly', false));
    }

    public function testSet(): void
    {
        $object = $this->createObject();
        PropertyAccess::set($object, 'text', 'new text');

        $this->assertSame('new text', PropertyAccess::get($object, 'text'));
    }

    public function testUnset(): void
    {
        $object = $this->createObject();
        $object->setText('text');

        PropertyAccess::unset($object, 'text');

        $this->assertNull(PropertyAccess::get($object, 'text'));
    }

    private function createObject(): Stub\PropertyObject
    {
        return new Stub\PropertyObject();
    }
}

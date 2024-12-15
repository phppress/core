<?php

declare(strict_types=1);

namespace PHPPress\Tests\Base;

use PHPPress\Exception\UnknownMethod;
use PHPPress\Exception\UnknownProperty;
use PHPPress\Tests\Base\Stub\MagicMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the MagicMethod class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('base')]
final class MagicMethodTest extends \PHPUnit\Framework\TestCase
{
    private MagicMethod|null $magicMethod = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->magicMethod ??= new MagicMethod();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->magicMethod = null;
    }

    public function testFailsForCallUnknownMethod(): void
    {
        $this->expectException(UnknownMethod::class);
        $this->expectExceptionMessage(
            'Unknown method: "Calling unknown method: PHPPress\Tests\Base\Stub\MagicMethod::unknown()."',
        );

        $this->magicMethod->unknown();
    }

    public function testFailsForGetUnknownProperty(): void
    {
        $this->expectException(UnknownProperty::class);
        $this->expectExceptionMessage('Getting unknown property: PHPPress\Tests\Base\Stub\MagicMethod::unknown."');

        $this->magicMethod->unknown;
    }

    public function testFailsForSetUnknownProperty(): void
    {
        $this->expectException(UnknownProperty::class);
        $this->expectExceptionMessage(
            'Unknown property: "Setting unknown property: PHPPress\Tests\Base\Stub\MagicMethod::unknown."',
        );

        $this->magicMethod->unknown = 'value';
    }

    public function testGet(): void
    {
        $this->assertSame('default', $this->magicMethod->Text);
    }

    public function testIsset(): void
    {
        $this->assertTrue(isset($this->magicMethod->Text));
        $this->assertNotEmpty($this->magicMethod->Text);

        $this->magicMethod->Text = '';

        $this->assertTrue(isset($this->magicMethod->Text));
        $this->assertEmpty($this->magicMethod->Text);

        $this->magicMethod->Text = null;

        $this->assertFalse(isset($this->magicMethod->Text));
        $this->assertEmpty($this->magicMethod->Text);
        $this->assertFalse(isset($this->magicMethod->unknownProperty));

        $isEmpty = empty($this->magicMethod->unknownProperty);

        $this->assertTrue($isEmpty);
    }

    public function testSet(): void
    {
        $value = 'new value';

        $this->magicMethod->Text = $value;

        $this->assertSame($value, $this->magicMethod->Text);
    }

    public function testUnset(): void
    {
        unset($this->magicMethod->Text);

        $this->assertFalse(isset($this->magicMethod->Text));
        $this->assertEmpty($this->magicMethod->Text);
    }
}

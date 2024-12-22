<?php

declare(strict_types=1);

namespace PHPPress\Tests\Http\Emitter;

use PHPPress\Http\Emitter\{ContentRange, ContentRangeUnit};

/**
 * Test case for the {@see ContentRange} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('http')]
final class ContentRangeTest extends \PHPUnit\Framework\TestCase
{
    public function testFromHeaderWithAsteriskLength(): void
    {
        $range = ContentRange::fromHeader('bytes 0-100/*');

        $this->assertInstanceOf(ContentRange::class, $range);
        $this->assertSame(ContentRangeUnit::BYTES, $range->unit);
        $this->assertSame(0, $range->first);
        $this->assertSame(100, $range->last);
        $this->assertSame('*', $range->length);
    }

    public function testFromHeaderWithEqualRange(): void
    {
        $range = ContentRange::fromHeader('bytes 5-5/10');
        $this->assertNotNull($range);
        $this->assertEquals(5, $range->first);
        $this->assertEquals(5, $range->last);
        $this->assertEquals(10, $range->length);
    }

    public function testFromHeaderWithInvalidFormat(): void
    {
        $this->assertNull(ContentRange::fromHeader('invalid'));
        $this->assertNull(ContentRange::fromHeader('bytes-0-100/500'));
        $this->assertNull(ContentRange::fromHeader('bytes 0-abc/500'));
    }

    public function testFromHeaderWithInvalidUnit(): void
    {
        $this->assertNull(ContentRange::fromHeader('invalid 0-100/500'));
    }

    public function testFromHeaderWithInvalidRange(): void
    {
        $this->assertNull(ContentRange::fromHeader('bytes 100-0/500'));
    }

    public function testFromHeaderWithNumericLength(): void
    {
        $range = ContentRange::fromHeader('bytes 0-100/500');

        $this->assertInstanceOf(ContentRange::class, $range);
        $this->assertSame(ContentRangeUnit::BYTES, $range->unit);
        $this->assertSame(0, $range->first);
        $this->assertSame(100, $range->last);
        $this->assertSame(500, $range->length);
    }

    public function testFromHeaderWithWhitespaceVariations(): void
    {
        $range = ContentRange::fromHeader('bytes  0-100/500');

        $this->assertSame(0, $range->first);
        $this->assertSame(100, $range->last);
    }

    public function testToString(): void
    {
        $range = new ContentRange(ContentRangeUnit::BYTES, 0, 100, 500);
        $this->assertSame('bytes 0-100/500', (string) $range);
    }
}

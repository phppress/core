<?php

declare(strict_types=1);

namespace PHPPress\Tests;

use PHPPress\Example;

final class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testExample(): void
    {
        $example = new Example();

        $this->assertTrue($example->getExample());
    }
}

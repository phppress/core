<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithConstructor
{
    public function __construct(private readonly ClassInstance $definitionClass) {}

    public function getDefinitionClass(): ClassInstance
    {
        return $this->definitionClass;
    }
}

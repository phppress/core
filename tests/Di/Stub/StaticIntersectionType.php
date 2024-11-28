<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class StaticIntersectionType
{
    public static function anotherDefinitionClassAndDefinitionClassInterfaceIntersection(
        AnotherDefinitionClass&DefinitionClassInterface $object,
    ): void {}

    public static function definitionClassInterfaceAndDefinitionClassIntersection(
        DefinitionClassInterface&AnotherDefinitionClass $object,
    ) {}
}

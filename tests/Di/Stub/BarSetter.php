<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class BarSetter
{
    private DefinitionClassInterface $definitionClass;

    public function getDefinitionClass(): DefinitionClassInterface
    {
        return $this->definitionClass;
    }

    public function setDefinitionClass(DefinitionClassInterface $definitionClass): void
    {
        $this->definitionClass = $definitionClass;
    }
}

<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class Alpha
{
    public function __construct(
        public Beta|null $beta = null,
        public DefinitionClassInterface|null $omega = null,
        public Unknown|null $unknown = null,
        public AbstractColor|null $color = null
    ) {
    }
}

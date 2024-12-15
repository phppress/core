<?php

declare(strict_types=1);

namespace PHPPress\Helper;

/**
 * Provides helper methods for configuring objects with properties and method calls.
 *
 * This class extends AbstractConfigure to provide a concrete implementation for object configuration.
 * It allows setting properties and calling methods on objects using an array configuration format.
 *
 * Example usage:
 * ```php
 * $object = Configure::configure(
 *     new MyClass(),
 *     [
 *         'property' => 'value',
 *         'setMethod()' => ['param1', 'param2']
 *     ]
 * );
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class Configure extends AbstractConfigure {}

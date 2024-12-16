<?php

declare(strict_types=1);

namespace PHPPress\Helper;

use PHPPress\Exception\{InvalidCall, UnknownProperty};
use PHPPress\Helper\Exception\Message;

use function is_subclass_of;
use function method_exists;
use function property_exists;

/**
 * Provides concrete implementation for {@see PropertyAccess}.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
abstract class AbstractPropertyAccess
{
    /**
     * Returns the value of an object property.
     *
     * @param object $object The object to retrieve the property value from.
     * @param string $name The property name.
     *
     * @throws UnknownProperty If the property is not defined.
     * @throws InvalidCall If the property is write-only.
     *
     * @return mixed The property value.
     *
     * {@see set()}
     */
    public static function get(object $object, string $name): mixed
    {
        $getter = "get{$name}";

        return match (true) {
            static::hasMethod($object, $getter) => $object->$getter(),
            static::hasMethod($object, "set{$name}") => throw new InvalidCall(
                Message::GETTING_WRITE_ONLY_PROPERTY->getMessage($object::class, $name),
            ),
            default => throw new UnknownProperty(Message::GETTING_UNKNOWN_PROPERTY->getMessage($object::class, $name)),
        };
    }

    /**
     * Returns a value indicating whether a method is defined.
     *
     * The default implementation is a call to php function `method_exists()`.
     *
     * You may override this method when you implemented the php magic method `__call()`.
     *
     * @param object $object The object to check.
     * @param string $name The method name.
     *
     * @return bool Whether the method is defined.
     */
    public static function hasMethod(object $object, string $name): bool
    {
        return method_exists($object, $name);
    }

    /**
     * Checks if a property is set, i.e. defined and not `null`.
     *
     * Do not call this method directly as it is a PHP magic method that will be implicitly called when executing
     * `isset($object->property)`.
     *
     * Note that if the property is not defined, `false` will be returned.
     *
     * @param object $object The object instance to check.
     * @param string $name The property name or the event name.
     *
     * @return bool Whether the named property is set (not `null`).
     *
     * {@see https://www.php.net/manual/en/function.isset.php}
     */
    public static function isset(object $object, string $name): bool
    {
        $getter = "get{$name}";

        return static::hasMethod($object, $getter) && $object->$getter() !== null;
    }

    /**
     * Checks if a class is a subclass of another class.
     *
     * This method is similar to the `is_subclass_of()` function, but it does not trigger autoload of classes.
     * Unlike the `instanceof` operator, this method works with both objects and class names.
     *
     * @param object|string $object The object or class name to check.
     * @param string $class The parent class or interface name to compare against.
     * @param bool $allowString Whether to allow checking against a class name as a string. Defaults to true.
     *
     * @return bool True if the object/class is a subclass of the specified class, false otherwise.
     */
    public static function isSubclassOf(object|string $object, string $class, bool $allowString = true): bool
    {
        return is_subclass_of($object, $class, $allowString);
    }

    /**
     * Determines whether a property can be retrieved from an object.
     *
     * A property is considered readable if:
     *
     * - The class has a getter method for the specified property name  (property name is case-insensitive);
     * - The class has a member variable with the specified name (when `$strictMode` is `false`);
     *
     * @param object $object The object to check for property readability.
     * @param string $name The name of the property to check.
     * @param bool $strictMode Whether to consider member variables as readable properties. Defaults to `true`.
     *
     * @return bool `true` if the property can be read, `false` otherwise.
     *
     * {@see isWritable()}
     */
    public static function isReadable(object $object, string $name, bool $strictMode = true): bool
    {
        $getter = "get{$name}";

        return $strictMode === false
            ? static::hasMethod($object, $getter)
            : self::propertyExists($object, $name) && static::hasMethod($object, $getter);
    }

    /**
     * Determines whether a property can be set on an object.
     *
     * A property is considered writable if:
     *
     * - The class has a setter method for the specified property name (property name is case-insensitive);
     * - The class has a member variable with the specified name (when `$strictMode` is `true`);
     *
     * @param object $object The object to check for property writ-ability.
     * @param string $name The name of the property to check.
     * @param bool $strictMode Whether to consider member variables as writable properties. Defaults to `true`.
     *
     * @return bool `true` if the property can be written, `false` otherwise.
     *
     * @see isReadable()
     */
    public static function isWritable(object $object, string $name, bool $strictMode = true): bool
    {
        $setter = "set{$name}";

        return $strictMode === false
            ? static::hasMethod($object, $setter)
            : self::propertyExists($object, $name) && static::hasMethod($object, $setter);
    }

    /**
     * Sets value of an object property.
     *
     * @param object $object The object to set the property value to.
     * @param string $name The property name or the event name.
     * @param mixed $value The property value.
     *
     * @throws InvalidCall If the property is read-only.
     * @throws UnknownProperty If the property is not defined.
     *
     * {@see get()}
     */
    public static function set(object $object, string $name, mixed $value): void
    {
        $setter = "set{$name}";

        match (true) {
            static::hasMethod($object, $setter) => $object->$setter($value),
            static::hasMethod($object, "get{$name}") => throw new InvalidCall(
                Message::SETTING_READ_ONLY_PROPERTY->getMessage($object::class, $name),
            ),
            default => throw new UnknownProperty(Message::SETTING_UNKNOWN_PROPERTY->getMessage($object::class, $name)),
        };
    }

    /**
     * Set an object property to null.
     *
     * Note that if the property is not defined, this method will do nothing.
     *
     * If the property is read-only, it will throw an exception.
     *
     * @param string $name The property name.
     *
     * @throws InvalidCall If the property is read only.
     * @throws UnknownProperty If the property is unknown.
     *
     * {@see https://www.php.net/manual/en/function.unset.php}
     */
    public static function unset(object $object, string $name): void
    {
        self::set($object, $name, null);
    }

    /**
     * Checks if a property exists in an object.
     *
     * @param object $object The object to check.
     * @param string $name The property name.
     *
     * @return bool Whether the property exists.
     */
    private static function propertyExists(object $object, string $name): bool
    {
        return property_exists($object, $name);
    }
}

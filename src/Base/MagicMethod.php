<?php

declare(strict_types=1);

namespace PHPPress\Base;

use PHPPress\Base\Exception\Message;
use PHPPress\Exception\{InvalidCall, UnknownProperty, UnknownMethod};
use PHPPress\Helper\PropertyAccess;

trait MagicMethod
{
    /**
     * Calls the named method which is not a class method.
     *
     * Do not call this method directly as it is a PHP magic method that will be implicitly called when an unknown
     * method is being invoked.
     *
     * @param string $name The method name.
     * @param array $params Method parameters.
     *
     * @throws UnknownMethod When calling unknown method.
     *
     * @return never This method will throw an exception.
     */
    public function __call(string $name, array $params): never
    {
        throw new UnknownMethod(Message::CALLING_UNKNOWN_METHOD->getMessage(static::class, $name));
    }

    /**
     * Returns the value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that will be implicitly called when executing
     * `$value = $object->property;`.
     *
     * @param string $name The property name.
     *
     * @return mixed The property value.
     *
     * @throws InvalidCall If the property is write-only.
     * @throws UnknownProperty If the property is not defined.
     *
     * @see __set()
     */
    public function __get(string $name): mixed
    {
        return PropertyAccess::get($this, $name);
    }

    /**
     * Checks if a property is set, i.e. defined and not `null`.
     *
     * Do not call this method directly as it is a PHP magic method that will be implicitly called when executing
     * `isset($object->property)`.
     *
     * Note that if the property is not defined, `false` will be returned.
     *
     * @param string $name The property name or the event name.
     *
     * @return bool Whether the named property is set (not `null`).
     *
     * @see https://www.php.net/manual/en/function.isset.php
     */
    public function __isset(string $name): bool
    {
        return PropertyAccess::isset($this, $name);
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that will be implicitly called when executing
     * `$object->property = $value;`.
     *
     * @param string $name The property name or the event name.
     * @param mixed $value The property value.
     *
     * @throws InvalidCall If the property is read-only.
     * @throws UnknownProperty If the property is not defined.
     *
     * @see __get()
     */
    public function __set(string $name, mixed $value): void
    {
        PropertyAccess::set($this, $name, $value);
    }

    /**
     * Sets an object property to null.
     *
     * Do not call this method directly as it is a PHP magic method that will be implicitly called when executing
     * `unset($object->property)`.
     *
     * Note that if the property is not defined, this method will do nothing.
     * If the property is read-only, it will throw an exception.
     *
     * @param string $name The property name.
     *
     * @throws InvalidCall If the property is read only.
     *
     * @see https://www.php.net/manual/en/function.unset.php
     */
    public function __unset(string $name): void
    {
        PropertyAccess::unset($this, $name);
    }
}

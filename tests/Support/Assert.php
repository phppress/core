<?php

declare(strict_types=1);

namespace PHPPress\Tests\Support;

use ReflectionClass;

use function str_replace;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class Assert
{
    /**
     * Asserting two strings equality ignoring line endings.
     *
     * @param string $expected The expected string.
     * @param string $actual The actual string.
     * @param string $message The message to display if the assertion fails.
     */
    public static function equalsWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);

        \PHPUnit\Framework\Assert::assertEquals($expected, $actual, $message);
    }

    /**
     * Gets an inaccessible object property using reflection.
     *
     * @param string|object $object The class name or object to get the property from.
     * @param string $propertyName The name of the property to get.
     */
    public static function inaccessibleProperty(string|object $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object);

        if (is_string($object) && $propertyName !== '') {
            return $reflection->getStaticPropertyValue($propertyName);
        }

        if ($propertyName !== '') {
            $property = $reflection->getProperty($propertyName);

            return $property->getValue($object);
        }

        return null;
    }

    /**
     * Sets an inaccessible object property using reflection.
     *
     * @param string|object $object The class name or object to set the property on.
     * @param string $propertyName The name of the property to set.
     * @param mixed $value The value to set.
     */
    public static function setInaccessibleProperty(string|object $object, string $propertyName, mixed $value): void
    {
        $reflection = new ReflectionClass($object);

        if (is_string($object) && $propertyName !== '') {
            $reflection->setStaticPropertyValue($propertyName, $value);

            return;
        }

        if ($propertyName !== '') {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }
    }
}

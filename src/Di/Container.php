<?php

declare(strict_types=1);

namespace PHPPress\Di;

use PHPPress\Di\Exception\{Message, NotInstantiable};
use PHPPress\Exception\{InvalidArgument, InvalidDefinition};
use Psr\Container\ContainerInterface;
use Throwable;
use TypeError;

use function array_key_exists;
use function gettype;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function str_contains;

/**
 * Container implements a dependency injection (DI) container for managing object creation and dependencies.
 *
 * Key features:
 * - Supports constructor and property injection.
 * - Manages object lifecycles (singleton and transient).
 * - Autowiring capabilities.
 * - Flexible definition of dependencies.
 *
 * The container can:
 * - Automatically resolve and instantiate class dependencies.
 * - Configure and inject dependencies.
 * - Cache and reuse singleton instances.
 *
 * Usage example:
 * ```php
 * $container = new Container();
 * $container->set(DatabaseInterface::class, MySqlDatabase::class);
 * $container->setSingleton(Logger::class);
 * $database = $container->get(DatabaseInterface::class);
 * ```
 *
 * For more information about DI, please refer to:
 * @see The [Martin Fowler's article](https://martinfowler.com/articles/injection.html).
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
class Container implements ContainerInterface
{
    /**
     * @var ReflectionFactory The factory for creating objects for dependency injection.
     *
     * @phpstan-ignore property.onlyRead
     */
    private ReflectionFactory $reflectionFactory {
        get => $this->reflectionFactory ??= new ReflectionFactory($this);
    }

    /**
     * Initializes the dependency injection container with optional definitions and singletons.
     *
     * @param array $definitions Pre-configured object definitions to register.
     * - Keys are class/interface names.
     * - Values are class definitions, callables, or instances.
     * - Default includes ContainerInterface mapped to the current container.
     *
     * @param array $singletons Pre-configured singleton object definitions.
     * - Keys are class/interface names.
     * - Values are singleton object configurations.
     * - Ensures only one instance of specified classes will be created.
     *
     * @throws InvalidDefinition If any provided definitions are invalid.
     *
     * ```php
     * $container = new Container(
     *     [
     *         LoggerInterface::class => FileLogger::class,
     *     ],
     *     [
     *         ConfigManager::class => $configManagerInstance,
     *     ],
     * );
     * ```
     */
    public function __construct(
        private array $definitions = [],
        private array $singletons = [],
    ) {
        $definitions += [
            ContainerInterface::class => self::class,
            self::class => $this,
        ];

        $this->setDefinitions($definitions);

        if ($this->singletons !== []) {
            $this->setSingletons($singletons);
        }
    }

    /**
     * Retrieves a dependency from the container.
     *
     * @param string $id The fully qualified class name, interface name, or alias of the dependency to retrieve.
     *
     * @throws InvalidDefinition If the requested dependency has an invalid configuration.
     * @throws NotInstantiable If the dependency cannot be instantiated or resolved.
     *
     * @return mixed The fully resolved and instantiated dependency.
     *
     * @phpstan-template T
     * @phpstan-param class-string<T> $id
     * @phpstan-return T
     */
    public function get(string $id): mixed
    {
        try {
            return $this->getInternal($id);
        } catch (TypeError $e) {
            throw new InvalidArgument($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the list of the object definitions or the loaded shared objects.
     *
     * @return array The list of the object definitions or the loaded shared objects (type or 'id' => definition or
     * instance).
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Checks if a dependency exists in the container.
     *
     * @param string $id The fully qualified class name, interface name, or alias to check.
     *
     * @return bool Returns true if the dependency exists in the container, false otherwise.
     *
     * This method checks for:
     * - Explicitly registered definitions.
     * - Registered singletons.
     * - Autowirable classes.
     *
     * @see set()
     */
    public function has(string $id): bool
    {
        $autowired = $this->reflectionFactory->canBeAutowired($id);

        return isset($this->definitions[$id]) || isset($this->singletons[$id]) || $autowired;
    }

    /**
     * Returns a value indicating whether the given name corresponds to a registered singleton.
     *
     * @param string $class The class name, interface name or alias name.
     * @param bool $checkInstance Whether to check if the singleton has been instantiated.
     *
     * @return bool Whether the given name corresponds to a registered singleton. If `$checkInstance` is `true`, the
     * method should return a value indicating whether the singleton has been instantiated.
     */
    public function hasSingleton(string $class, bool $checkInstance = false): bool
    {
        return $checkInstance ? isset($this->singletons[$class]) : array_key_exists($class, $this->singletons);
    }

    /**
     * Registers a class definition with the container.
     *
     * @param string $class The fully qualified class name, interface name, or alias to register.
     * @param mixed $definitions The definition for the class. Can be:
     * - A callable function with signature `function ($container, $params)`.
     * - An array of configuration options.
     * - A string representing another class name.
     * - An object instance.
     *
     * @throws InvalidDefinition If the provided definition is invalid or cannot be processed.
     *
     * @return static The container instance for method chaining.
     *
     * ```php
     * $container->set(
     *     LoggerInterface::class,
     *     static function($container) {
     *         return new FileLogger('/path/to/logs');
     *     },
     * );
     *
     * $container->set(
     *     Database::class,
     *     [
     *         'class' => MySqlDatabase::class,
     *         'host' => 'localhost',
     *         'port' => 3306,
     *     ],
     * );
     * ```
     */
    public function set(string $class, mixed $definitions = []): static
    {
        $this->definitions[$class] = $this->normalizeDefinition($class, $definitions);

        unset($this->singletons[$class]);

        return $this;
    }

    /**
     * Registers a class definition as a singleton within the container.
     *
     * @param string $class The fully qualified class name, interface name, or alias to register.
     * @param mixed $definition The definition for the singleton. Can include:
     * - Property configurations.
     * - Method call configurations.
     * - Initialization parameters.
     *
     * @throws InvalidDefinition If the provided definition is invalid or cannot be processed.
     *
     * @return static The container instance for method chaining.
     *
     * Note: Singleton instances are created once and reused for subsequent retrievals.
     *
     * ```php
     * $container->setSingleton(
     *     ConfigManager::class,
     *     [
     *         'configPath' => '/etc/myapp/config.json',
     *         'loadEnvironment()' => ['production']
     *     ],
     * );
     * ```
     *
     * @see set()
     */
    public function setSingleton(string $class, mixed $definition = []): static
    {
        $this->definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->singletons[$class] = null;

        return $this;
    }

    /**
     * Returns an instance of the requested class.
     *
     * Note that if the class is declared to be singleton by calling [[setSingleton()]], the same instance of the class
     * will be returned each time this method is called.
     *
     * @param string $id The class Instance, name, or an alias name (e.g. `foo`) that was previously registered
     * via [[set()]] or [[setSingleton()]].
     *
     * @throws InvalidDefinition If the class cannot be recognized or correspond to an invalid definition.
     * @throws NotInstantiable If resolved to an abstract class or an interface.
     * @throws Throwable In case of circular references.
     *
     * @return mixed The entry of the container.
     */
    private function getInternal(string $id): mixed
    {
        if ($id === ContainerInterface::class || $id === self::class) {
            return $this;
        }

        if ($this->hasSingleton($id, true)) {
            return $this->singletons[$id];
        }

        $definition = $this->definitions[$id] ?? null;
        $entry = null;

        if ($definition === null) {
            return $this->reflectionFactory->create($id);
        }

        if (is_callable($definition, true)) {
            $entry = $this->reflectionFactory->invoke($definition);
        } elseif (is_array($definition)) {
            $concrete = $definition['class'];
            $definition = $this->definitions[$concrete] ?? $definition;

            unset($definition['class']);

            $entry = $this->reflectionFactory->create($concrete, $definition);
        } elseif (is_object($definition)) {
            return $this->singletons[$id] = $definition;
        }

        if (array_key_exists($id, $this->singletons)) {
            $this->singletons[$id] = $entry;
        }

        return $entry;
    }

    /**
     * Normalizes the class definition.
     *
     * @param string $class The class name.
     * @param mixed $definition The class definition.
     *
     * @throws InvalidDefinition If the definition is invalid.
     *
     * @return array|callable|string|object The normalized class definition.
     */
    private function normalizeDefinition(string $class, mixed $definition): array|callable|string|object
    {
        if ($definition === null || $definition === []) {
            return ['class' => $class];
        }

        if (is_string($definition)) {
            if ($this->reflectionFactory->canBeAutowired($definition) === false) {
                throw new InvalidDefinition(Message::DEFINITION_INVALID->getMessage($class, $definition));
            }

            return ['class' => $definition];
        }

        if ($definition instanceof Instance) {
            return ['class' => $definition->id];
        }

        if (is_callable($definition, true) || is_object($definition)) {
            return $definition;
        }

        if (is_array($definition)) {
            if (!isset($definition['class']) && isset($definition['__class'])) {
                $definition['class'] = $definition['__class'];

                unset($definition['__class']);
            }

            if (!isset($definition['class'])) {
                if (str_contains($class, '\\')) {
                    $definition['class'] = $class;
                } else {
                    throw new InvalidDefinition(Message::DEFINITION_REQUIRES_CLASS_OPTION->getMessage());
                }
            }

            return $definition;
        }

        throw new InvalidDefinition(Message::DEFINITION_TYPE_UNSUPPORTED->getMessage(gettype($definition)));
    }

    /**
     * Registers multiple class definitions within the container.
     *
     * @param array $definitions Definitions array with two supported formats:
     * 1. Simple format: [className]
     * 2. Complex format: [className => definitionConfig]
     *
     * @throws InvalidDefinition If any definition is invalid.
     *
     * ```php
     * // Simple format
     * $this->setDefinitions([UserService::class, LoggerInterface::class]);
     *
     * // Complex format
     * $this->setDefinitions(
     *     [
     *         DatabaseInterface::class => MySqlDatabase::class,
     *         CacheInterface::class => [
     *             'class' => RedisCache::class,
     *             'host' => 'localhost'
     *         ],
     *     ],
     * );
     * ```
     *
     * @see set() to know more about possible values of definitions.
     */
    private function setDefinitions(array $definitions): void
    {
        foreach ($definitions as $class => $definition) {
            match (is_int($class) && is_string($definition)) {
                true => $this->set($definition),
                default => $this->set($class, $definition),
            };
        }
    }

    /**
     * Registers multiple class definitions as singletons within the container.
     *
     * @param array $definitions Singleton definitions array with two supported formats:
     *   1. Simple format: [singletonClassName]
     *   2. Complex format: [singletonClassName => definitionConfig]
     *
     * @throws InvalidDefinition If any singleton definition is invalid.
     *
     * ```php
     * // Simple format
     * $this->setSingletons([ConfigManager::class, Logger::class]);
     *
     * // Complex format
     * $this->setSingletons(
     *     [
     *         ConfigManager::class => [
     *             'configPath' => '/app/config.json'
     *         ],
     *     ],
     * );
     * ```
     *
     * @see setSingleton() to know more about possible values of definitions.
     * @see setDefinitions() for allowed formats of $singletons parameter.
     */
    private function setSingletons(array $definitions): void
    {
        foreach ($definitions as $class => $definition) {
            match (is_int($class) && is_string($definition)) {
                true => $this->setSingleton($definition),
                default => $this->setSingleton($class, $definition),
            };
        }
    }
}

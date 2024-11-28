<?php

declare(strict_types=1);

namespace PHPPress\Di;

use PHPPress\Di\Exception\{Message, NotInstantiable};
use PHPPress\Exception\InvalidConfig;
use PHPPress\Helper\Arr;
use Psr\Container\ContainerInterface;
use Throwable;

use function array_key_exists;
use function array_merge;
use function gettype;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function str_contains;

/**
 * Container implements a [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) container.
 *
 * A dependency injection (DI) container is an object that knows how to instantiate and configure objects and all their
 * dependent objects.
 *
 * Container supports constructor injection as well as property injection.
 *
 * To use Container, you first need to set up the class dependencies by calling [[set()]].
 *
 * You then call [[get()]] to create a new class object. The Container will automatically instantiate dependent objects,
 * inject them into the object being created, configure, and finally return the newly created object.
 *
 * Below is an example of using Container:
 *
 * ```php
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
     * @var array Object definitions indexed by their types.
     */
    private array $definitions = [];
    /**
     * @var array Singleton objects indexed by their types.
     */
    private array $singletons = [];
    /**
     * @var ReflectionFactory|null The factory for creating objects for dependency injection.
     */
    private ReflectionFactory|null $reflectionFactory = null;

    /**
     * Removes all previously registered services from the container.
     */
    public function clear(): void
    {
        $this->definitions = [];
        $this->singletons = [];
    }

    /**
     * Creates a new instance of the specified class with defined properties and method calls.
     *
     * @param string $class The fully qualified class name to instantiate.
     * @param array $definitions An associative array defining constructor parameters, property values, and method
     * calls:
     *  - `'__construct()' => ['param1', 'param2']`: Calls the constructor with the specified parameters. If a parameter
     *    is omitted, the class's default value is used. Named arguments can also be used.
     *    e.g., `['__construct()' => ['paramName' => 'value']]`.
     *  - `'propertyName' => 'value'`: Sets the specified property to the given value.
     *  - `'methodName()' => ['param']`: Invokes the method with the provided parameters.
     *
     * @throws InvalidConfig If the class cannot be recognized or the definitions are invalid.
     * @throws NotInstantiable If the resolved class is abstract or an interface.
     * @throws Throwable If a circular reference is detected during instantiation.
     *
     * @return mixed The newly created and configured instance of the specified class.
     */
    public function create(string $class, array $definitions = []): mixed
    {
        return $this->getInternal($class, $definitions);
    }

    /**
     * Retrieves a dependency from the container.
     *
     * @param string $id The dependency 'id' (typically a class or interface name).
     *
     * @throws NotInstantiable If the dependency cannot be resolved.
     *
     * @return mixed The resolved dependency.
     */
    public function get(string $id): mixed
    {
        try {
            return $this->getInternal($id);
        } catch (NotInstantiable $e) {
            throw new NotInstantiable($e->getMessage());
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
     * Returns a value indicating whether the container has the definition of the specified name.
     *
     * @param string $id The class name, interface name or alias name.
     *
     * @return bool Whether the container has the definition of the specified name.
     *
     * @see set()
     */
    public function has(string $id): bool
    {
        $autowired = $this->reflectionFactory()->canBeAutowired($id);

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
     * Invoke a callback with resolving dependencies in parameters.
     *
     * This method allows invoking a callback and let type hinted parameter names to be resolved as objects of the
     * Container. It additionally allows calling function using named parameters.
     *
     * For example, the following callback may be invoked using the Container to resolve the formatter dependency:
     *
     * ```php
     * ```
     *
     * This will pass the string `'Hello World!'` as the first param, and a formatter instance created by the DI
     * container as the second param to the callable.
     *
     * @param callable $callback The callable to be invoked.
     * @param array $params The array of parameters for the function. This can be either a list of parameters or an
     * associative array representing named function parameters.
     *
     * @throws InvalidConfig If a dependency cannot be resolved, or if a dependency cannot be fulfilled.
     * @throws NotInstantiable If the dependency cannot be resolved.
     * @throws Throwable If the callback is not valid, callable.
     *
     * @return mixed The callback return value.
     */
    public function invoke(callable $callback, array $params = []): mixed
    {
        try {
            return call_user_func_array(
                $callback,
                $this->reflectionFactory()->resolveCallableDependencies($callback, $params),
            );
        } catch (Throwable $e) {
            throw new NotInstantiable(Message::INSTANTIATION_FAILED->getMessage($e->getMessage()));
        }
    }

    /**
     * Resolve dependencies for a function.
     *
     * This method can be used to implement similar functionality as provided by [[invoke()]] in other components.
     *
     * @param callable $callback The callable to be invoked.
     * @param array $params The array of parameters for the function can be either numeric or associative.
     *
     * @throws InvalidConfig If a dependency cannot be resolved, or if a dependency cannot be fulfilled.
     * @throws Throwable If the callback is not valid, callable.
     *
     * @return array The resolved dependencies.
     */
    public function resolveCallableDependencies(callable $callback, array $params = []): array
    {
        return $this->reflectionFactory()->resolveCallableDependencies($callback, $params);
    }

    /**
     * Registers a class definition with this container.
     *
     * For example,
     *
     * ```php
     * ```
     *
     * If a class definition with the same name already exists, it will be overwritten with the new one.
     * You may use [[has()]] to check if a class definition already exists.
     *
     * @param string $class The class name, interface name or alias name.
     * @param mixed $definitions The definition associated with `$class`. It can be one of the following:
     * - a PHP callable: The callable will be executed when [[get()]] is invoked. The signature of the callable should
     *   be `function ($container, $params, $config)`, where `$params` stands for the list of constructor parameters,
     *   `$config` the object definition, and `$container` the container object. The return value of the callable will
     *   be returned by [[get()]] as the object instance requested.
     * - a definition array: the array contains name-value pairs that will be used to initialize the property or method
     *   values of the newly created object when [[get()]] is called. The `class` element stands for the class of the
     *   object to be created. If `class` is not specified, `$class` will be used as the class name.
     * - a string: a class name, an interface name or an alias name.
     *
     * @throws InvalidConfig if the definition is invalid.
     *
     * @return static The container itself.
     */
    public function set(string $class, mixed $definitions = []): static
    {
        $this->definitions[$class] = $this->normalizeDefinition($class, $definitions);

        unset($this->singletons[$class]);

        return $this;
    }

    /**
     * Registers class definitions within this container.
     *
     * @param array $definitions The array of definitions. There are two allowed formats of an array.
     * The first format:
     *  - key: the class name, interface name or alias name. The key will be passed to the [[set()]] method as a first
     *    argument `$class`.
     *  - value: the definition associated with `$class`. Possible values are described in
     *    [[set()]] documentation for the `$definition` parameter. It Will be passed to the [[set()]] method as the
     *    second argument `$definition`.
     *
     * Example:
     * ```php
     * ```
     *
     * The second format:
     *  - key: class name, interface name or alias name. The key will be passed to the [[set()]] method as a first
     *    argument `$class`.
     *  - value: array of two elements. The first element will be passed the [[set()]] method as the second argument
     *    `$definition`, the second one — as `$params`.
     *
     * Example:
     * ```php
     * ```
     *
     * @throws InvalidConfig If a definition is invalid.
     *
     * @see set() to know more about possible values of definitions.
     */
    public function setDefinitions(array $definitions): void
    {
        if (Arr::isList($definitions)) {
            return;
        }

        foreach ($definitions as $class => $definition) {
            $this->set($class, $definition);
        }
    }

    /**
     * Registers a class definition with this container and marks the class as a singleton class.
     *
     * This method is similar to [[set()]] except that classes registered via this method will only have one instance.
     *
     * Each time [[get()]] is called, the same instance of the specified class will be returned.
     *
     * @param string $class The class name, interface name or alias name.
     * @param array $definition The property values (name-value pairs) given in terms of property names or methods.
     * - keys are property names with their corresponding values.
     *   e.g. `'propertyName' => 'value'`.
     * - method names (ending with `()`) are keys with an array of parameters as values.
     *   e.g. `'methodName()' => ['param1', 'param2']`.
     *
     * @throws InvalidConfig If the definition is invalid.
     *
     * @return static The container itself.
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
     * Registers class definitions as singletons within this container by calling [[setSingleton()]].
     *
     * @param array $singletons The array of singleton definitions. See [[setDefinitions()]] for allowed formats of an
     * array.
     *
     * @throws InvalidConfig If a definition is invalid.
     *
     * @see setSingleton() to know more about possible values of definitions.
     * @see setDefinitions() for allowed formats of $singletons parameter.
     */
    public function setSingletons(array $singletons): void
    {
        if (Arr::isList($singletons)) {
            return;
        }

        foreach ($singletons as $class => $definition) {
            $this->setSingleton($class, $definition);
        }
    }

    /**
     * Returns an instance of the requested class.
     *
     * Note that if the class is declared to be singleton by calling [[setSingleton()]], the same instance of the class
     * will be returned each time this method is called.
     *
     * @param string $id The class Instance, name, or an alias name (e.g. `foo`) that was previously registered
     * via [[set()]] or [[setSingleton()]].
     * @param array $definitions An associative array defining constructor parameters, property values, and method
     * calls:
     *  - `'__construct()' => ['param1', 'param2']`: Calls the constructor with the specified parameters. If a parameter
     *    is omitted, the class's default value is used. Named arguments can also be used.
     *    e.g., `['__construct()' => ['paramName' => 'value']]`.
     *  - `'propertyName' => 'value'`: Sets the specified property to the given value.
     *  - `'methodName()' => ['param']`: Invokes the method with the provided parameters.
     *
     * @throws InvalidConfig If the class cannot be recognized or correspond to an invalid definition.
     * @throws NotInstantiable If resolved to an abstract class or an interface.
     * @throws Throwable In case of circular references.
     *
     * @return mixed The entry of the container.
     */
    protected function getInternal(string $id, array $definitions = []): mixed
    {
        if ($this->hasSingleton($id, true)) {
            return $this->singletons[$id];
        }

        if (!isset($this->definitions[$id])) {
            return $this->reflectionFactory()->create($id, $definitions);
        }

        $definition = $this->definitions[$id];
        $entry = null;

        if (is_callable($definition, true)) {
            $entry = $definition($this, $definitions);
        } elseif (is_array($definition)) {
            $concrete = $definition['class'];

            unset($definition['class']);

            $definitions = array_merge($definition, $definitions);

            $entry = match ($concrete === $id) {
                true => $this->reflectionFactory()->create($id, $definitions),
                default => $this->create($concrete, $definitions),
            };
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
     * @throws InvalidConfig If the definition is invalid.
     *
     * @return array|callable|string|object The normalized class definition.
     */
    protected function normalizeDefinition(string $class, mixed $definition): array|callable|string|object
    {
        if (empty($definition)) {
            return ['class' => $class];
        }

        if (is_string($definition)) {
            if ($this->reflectionFactory()->canBeAutowired($definition) === false) {
                throw new InvalidConfig(Message::DEFINITION_INVALID->getMessage($class, $definition));
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
                    throw new InvalidConfig(Message::DEFINITION_REQUIRES_CLASS_OPTION->getMessage());
                }
            }

            return $definition;
        }

        throw new InvalidConfig(Message::DEFINITION_TYPE_UNSUPPORTED->getMessage(gettype($definition)));
    }

    protected function reflectionFactory(): ReflectionFactory
    {
        if ($this->reflectionFactory === null) {
            $this->reflectionFactory = new ReflectionFactory($this);
        }

        return $this->reflectionFactory;
    }
}

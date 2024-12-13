<?php

declare(strict_types=1);

namespace PHPPress\Di;

use Closure;
use Exception;
use PHPPress\Di\Exception\{Message, NotInstantiable};
use PHPPress\Exception\InvalidDefinition;
use PHPPress\Helper\Arr;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Throwable;

use function array_key_exists;
use function is_array;
use function is_object;

/**
 * Reflection Factory for dependency creation and resolution.
 *
 * This class uses PHP reflection to analyze, create, and automatically inject dependencies into objects. It provides
 * advanced dependency injection capabilities, enabling class instance creation with automatic dependency resolution.
 *
 * Key Features:
 * - Automatic dependency resolution.
 * - Support for complex parameter types (union, intersection).
 * - Reflection and dependency caching.
 * - Dependency configuration validation.
 *
 * Provides a robust mechanism for:
 * - Instantiating classes with automatic constructor dependency injection.
 * - Resolving dependencies for callable methods and functions.
 * - Handling various type hinting scenarios.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
class ReflectionFactory
{
    /**
     * @var array Cached dependencies indexed by class/interface names.
     */
    private array $dependencies = [];
    /**
     * @var array Cached ReflectionClass objects indexed by class/interface names.
     */
    private array $reflections = [];

    public function __construct(private readonly Container $container) {}

    /**
     * Determines if a class can be automatically instantiated.
     *
     * Checks whether a given class or interface can be instantiated without manual configuration. Utilizes reflection
     * to verify the instantiability of the class.
     *
     * @param string $id The fully qualified class or interface name.
     *
     * @return bool Returns true if the class can be autowired, false otherwise.
     *
     * // Check if a class can be automatically instantiated
     * ```php
     * $canCreateUser = $reflectionFactory->canBeAutowired(User::class);
     * ```
     */
    public function canBeAutowired(string $id): bool
    {
        try {
            if (array_key_exists($id, $this->reflections)) {
                return $this->reflections[$id]->isInstantiable();
            }

            return new ReflectionClass($id)->isInstantiable();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Creates an instance of a class with resolved dependencies.
     *
     * This method builds an object by performing the following tasks:
     * 1. Retrieves constructor dependencies for the class.
     * 2. Merges default dependencies with custom dependencies.
     * 3. Resolves all dependencies.
     * 4. Instantiates the object with resolved dependencies.
     *
     * @param string $class The fully qualified class name to instantiate-
     * @param array $definitions Additional property configurations and constructor parameters.
     *   - Use `__construct()` key for constructor parameters.
     *   - Other keys represent property setter configurations.
     *
     * @throws InvalidDefinition If dependency definition is incorrect.
     * @throws NotInstantiable If the class cannot be instantiated.
     *
     * @return object The fully constructed and configured object instance.
     *
     * // Basic usage
     * ```php
     * $user = $reflectionFactory->create(User::class);
     * ```
     *
     * // With custom constructor parameters
     * ```php
     * $user = $reflectionFactory->create(
     *     User::class,
     *     [
     *         '__construct()' => ['John Doe', 30],
     *         'setRole()' => ['admin'],
     *     ],
     * );
     * ```
     */
    public function create(string $class, array $definitions = []): object
    {
        /** @var ReflectionClass $reflection */
        [$reflection, $dependencies] = $this->getDependencies($class, $definitions);

        $addDependencies = $definitions['__construct()'] ?? [];

        unset($definitions['__construct()']);

        $mergeDependencies = $this->mergeDependencies($dependencies, $addDependencies);
        $resolveDependencies = $this->resolveDependencies($mergeDependencies);

        if ($this->canBeAutowired($class) === false) {
            throw new NotInstantiable(Message::INSTANTIATION_FAILED->getMessage($class));
        }

        $object = $reflection->newInstanceArgs($resolveDependencies);

        return ConfigurableFactory::configure($object, $definitions);
    }

    /**
     * Resolves dependencies for a callable (function, method, or closure).
     *
     * Analyzes the callable's signature and automatically resolves its dependencies based on type hints, available
     * parameters, and the dependency injection container.
     *
     * Supports:
     * - Static methods.
     * - Instance methods.
     * - Closures.
     * - Regular functions.
     * - Variadic parameters.
     *
     * @param array|callable|Closure|string $callback The callable to resolve dependencies for.
     * @param array $params Optional explicit parameters to override automatic resolution.
     *
     * @throws InvalidDefinition If a dependency cannot be resolved or if dependencies are in an invalid format.
     * @throws Throwable If the callback is not valid or callable.
     *
     * @return array Resolved dependencies ready to be used as function arguments.
     *
     * // Resolve dependencies for a function
     * ```php
     * $args = $reflectionFactory->resolveCallableDependencies(
     *     [UserService::class, 'createUser'],
     *     ['username' => 'john_doe']
     * );
     * ```
     *
     * // Resolve dependencies for a closure
     * ```php
     * $args = $reflectionFactory->resolveCallableDependencies(
     *     fn(UserRepository $repo) => $repo->findAll()
     * );
     * ```
     */
    public function resolveCallableDependencies(array|callable|Closure|string $callback, array $params = []): array
    {
        $reflection = match (true) {
            is_array($callback) => new ReflectionMethod($callback[0], $callback[1]),
            is_object($callback) && !$callback instanceof Closure => new ReflectionMethod($callback, '__invoke'),
            default => new ReflectionFunction($callback),
        };

        return $this->resolveMethodParameters($reflection, $params);
    }

    /**
     * Extracts class names from a reflection type, handling various type scenarios.
     *
     * This method supports:
     * - Simple named types.
     * - Union types.
     * - Intersection types.
     *
     * Prioritizes non-built-in types (classes and interfaces).
     *
     * @param ReflectionIntersectionType|ReflectionNamedType|ReflectionType|null $reflectionType The reflection type to
     * analyze.
     *
     * @return array List of extracted class names, empty array if no class names found.
     */
    private function getClassName(
        ReflectionIntersectionType|ReflectionNamedType|ReflectionType|null $reflectionType,
    ): array {
        if ($reflectionType === null) {
            return [];
        }

        $classes = [];

        if ($reflectionType instanceof ReflectionNamedType && $reflectionType->isBuiltin() === false) {
            $classes[] = $reflectionType->getName();
        }

        if ($reflectionType instanceof ReflectionUnionType || $reflectionType instanceof ReflectionIntersectionType) {
            /** @var ReflectionNamedType[] $types */
            $types = $reflectionType->getTypes();

            foreach ($types as $type) {
                if (!$type->isBuiltin()) {
                    $classes[] = $type->getName();
                }
            }
        }

        return $classes;
    }

    /**
     * Retrieves and caches dependencies for a specific class.
     *
     * Performs the following operations:
     * 1. Checks for cached dependencies.
     * 2. Reflects on the class constructor.
     * 3. Analyzes constructor parameters.
     * 4. Caches reflection and dependencies for future use.
     *
     * @param string $class The fully qualified class name to inspect.
     *
     * @throws NotInstantiable If the class cannot be reflected or instantiated.
     *
     * @return array A tuple containing:
     * - ReflectionClass instance
     * - Array of dependencies
     */
    private function getDependencies(string $class, array $params = []): array
    {
        if (isset($this->reflections[$class])) {
            return [$this->reflections[$class], $this->dependencies[$class]];
        }

        $dependencies = [];

        try {
            $reflection = new ReflectionClass($class);
            $this->reflections[$class] = $reflection;
        } catch (ReflectionException $e) {
            throw new NotInstantiable(Message::INSTANTIATION_FAILED->getMessage($class));
        }

        $reflectionMethod = match ($reflection->isInternal()) {
            true => null,
            default => $reflection->getConstructor(),
        };

        if ($reflectionMethod !== null) {
            $dependencies = $this->resolveMethodParameters($reflectionMethod, $params['__construct()'] ?? []);
        }

        $this->dependencies[$class] = $dependencies;

        return [$reflection, $dependencies];
    }

    /**
     * Merges dependency arrays with intelligent combination strategy.
     *
     * Handles two merge scenarios:
     * - Numeric array: Replaces entire dependencies.
     * - Associative array: Replaces specific dependencies.
     *
     * @param array $a Original dependencies array.
     * @param array $b New dependencies to merge.
     *
     * @return array Merged dependencies array.
     */
    private function mergeDependencies(array $a, array $b): array
    {
        if ($b !== [] && Arr::isList($b)) {
            return $b;
        }

        return $a;
    }

    /**
     * Resolves dependencies for built-in type parameters.
     *
     * Resolution strategy:
     * 1. Use explicitly provided parameter.
     * 2. Shift first available parameter.
     * 3. Use default value if available.
     * 4. Throw exception if no resolution possible.
     *
     * @param ReflectionParameter $reflectionParameter Parameter being resolved.
     * @param string $name Name of the parameter.
     * @param int|string $key Key of the parameter in the parameters array.
     * @param array $params Available parameters, passed by reference.
     *
     * @throws InvalidDefinition If required parameter cannot be resolved.
     *
     * @return mixed Resolved parameter value.
     */
    private function resolveBuiltInType(
        ReflectionParameter $reflectionParameter,
        string $name,
        int|string $key,
        array &$params,
    ): mixed {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];

            unset($params[$key]);

            return $value;
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        throw new InvalidDefinition(
            Message::PARAMETER_MISSING->getMessage(
                $name,
                $reflectionParameter->getDeclaringFunction()->getName(),
            ),
        );
    }

    /**
     * Recursively resolves dependencies by replacing placeholders with actual object instances.
     *
     * Handles complex dependency scenarios:
     * - Resolves Instance placeholders using the container.
     * - Supports nested dependency resolution.
     * - Handles optional dependencies.
     *
     * @param array $dependencies List of dependencies to resolve.
     *
     * @throws Exception If dependency resolution fails.
     * @throws InvalidDefinition For unresolvable dependencies.
     * @throws NotInstantiable For instantiation failures.
     *
     * @return array Fully resolved dependencies.
     */
    private function resolveDependencies(array $dependencies): array
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                $dependencies[$index] = $this->container->get($dependency->id);
            } elseif (is_array($dependency)) {
                $dependencies[$index] = $this->resolveDependencies($dependency);
            }
        }

        return $dependencies;
    }

    /**
     * Resolves a single dependency for a method or function parameter.
     *
     * Comprehensive resolution strategy:
     * 1. Check for explicitly provided parameter.
     * 2. Use default value if available.
     * 3. Attempt to resolve via dependency container.
     * 4. Fallback to built-in type resolution.
     *
     * @param ReflectionParameter $reflectionParameter Parameter being resolved.
     * @param array $classNames Potential class names for the parameter.
     * @param string $name Name of the parameter.
     * @param int|string $key Key of the parameter in the parameters array.
     * @param array $params Available parameters, passed by reference.
     *
     * @throws InvalidDefinition If dependency cannot be resolved.
     *
     * @return mixed Resolved dependency value.
     *
     * @phpstan-param list<class-string> $classNames
     */
    private function resolveDependency(
        ReflectionParameter $reflectionParameter,
        array $classNames,
        string $name,
        int|string $key,
        array &$params,
    ): mixed {
        foreach ($classNames as $className) {
            if (array_key_exists($key, $params)) {
                $value = $params[$key];

                unset($params[$key]);

                return $value;
            }

            if ($this->container->has($className) === false && $reflectionParameter->isDefaultValueAvailable()) {
                return $reflectionParameter->getDefaultValue();
            }

            try {
                return $this->container->get($className);
            } catch (NotInstantiable) {
                continue;
            }
        }

        return $this->resolveBuiltInType($reflectionParameter, $name, $key, $params);
    }

    /**
     * Resolves method parameters using reflection, supporting constructors, invoke methods, and custom methods.
     *
     * Performs a comprehensive analysis of method parameters:
     * 1. Reflects on the method parameters.
     * 2. Resolves dependencies for each parameter.
     * 3. Handles variadic and non-variadic arguments.
     * 4. Resolves instance dependencies.
     *
     * @param ReflectionMethod|ReflectionFunction $method The method to analyze.
     * @param array $params Additional parameters to use in resolution.
     *
     * @return array Resolved method arguments.
     */
    private function resolveMethodParameters(ReflectionMethod|ReflectionFunction $method, array $params = []): array
    {
        $args = [];

        $isAsociativeParams = Arr::isAssociative($params);

        $this->validateDependencies($params);

        foreach ($method->getParameters() as $key => $reflectionParameter) {
            $type = $reflectionParameter->getType();
            $name = $reflectionParameter->getName();

            $key = $isAsociativeParams ? $name : $key;

            $classNames = $this->getClassName($type);

            if ($reflectionParameter->isVariadic() === true) {
                $resolvedParams = $this->resolveDependency($reflectionParameter, $classNames, $name, $key, $params);
                $varadicArgs = is_array($resolvedParams) ? $resolvedParams : [$resolvedParams];

                $args = [...$args, ...$varadicArgs];
            } else {
                $args[] = $this->resolveDependency($reflectionParameter, $classNames, $name, $key, $params);
            }
        }

        return $this->resolveDependencies($args);
    }

    /**
     * Validates the format of dependencies passed during object creation.
     *
     * Ensures dependencies are provided in a valid format:
     * - Numeric (indexed) array.
     * - Associative array.
     *
     * @param array $parameters Dependencies to validate.
     *
     * @throws InvalidDefinition If dependencies are in an invalid format.
     */
    private function validateDependencies(array $parameters): void
    {
        if (Arr::isList($parameters)) {
            return;
        }

        if (Arr::isAssociative($parameters)) {
            return;
        }

        throw new InvalidDefinition(Message::DEPENDENCIES_IDX_NAME_POSITION->getMessage());
    }
}

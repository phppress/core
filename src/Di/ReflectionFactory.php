<?php

declare(strict_types=1);

namespace PHPPress\Di;

use Closure;
use Exception;
use PHPPress\Di\Exception\{Message, NotInstantiable};
use PHPPress\Exception\InvalidConfig;
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
use function array_replace;
use function array_shift;
use function array_values;
use function count;
use function is_array;
use function is_object;

/**
 * Provides methods for creating and resolving dependencies using reflection.
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
    /**
     * @var bool Whether to attempt to resolve elements in array dependencies.
     */
    private bool $resolveArrays = true;

    public function __construct(private readonly Container $container) {}

    /**
     * Checks if a class can be autowired.
     *
     * @param string $id The class name or interface.
     *
     * @return bool Whether the class can be autowired.
     */
    public function canBeAutowired(string $id): bool
    {
        try {
            $reflection = $this->getReflection($id);

            return $reflection->isInstantiable();
        } catch (ReflectionException) {
            return false;
        }
    }
    /**
     * Creates an instance of the specified class.
     *
     * @param string $class The class name.
     * @param array $definitions The property values (name-value pairs) given in terms of property names or methods.
     *
     * @throws NotInstantiable If the class is not instantiable.
     * @throws InvalidConfig If there's an invalid configuration.
     *
     * @return object The created instance.
     */
    public function create(string $class, array $definitions = []): object
    {
        /** @var ReflectionClass $reflection */
        [$reflection, $dependencies] = $this->getDependencies($class);

        $addDependencies = $definitions['__construct()'] ?? [];

        unset($definitions['__construct()']);

        $this->validateDependencies($addDependencies);

        $mergeDependencies = $this->mergeDependencies($dependencies, $addDependencies);
        $resolveDependencies = $this->resolveDependencies($mergeDependencies, $reflection);

        if ($reflection->isInstantiable() === false) {
            throw new NotInstantiable(Message::INSTANTIATION_FAILED->getMessage($class));
        }

        $definitions = $this->resolveDependencies($definitions);
        $object = $reflection->newInstanceArgs($resolveDependencies);

        return ConfigurableFactory::configure($object, $definitions);
    }

    /**
     * Resolve dependencies for a function.
     *
     * This method can be used to implement similar functionality as provided by [[invoke()]] in other components.
     *
     * @param callable|Closure|string $callback Callable to be invoked.
     * @param array $params The array of parameters for the function can be either numeric or associative.
     *
     * @throws InvalidConfig If a dependency cannot be resolved, or if a dependency cannot be fulfilled.
     * @throws Throwable If the callback is not valid, callable.
     *
     * @return array The resolved dependencies.
     */
    public function resolveCallableDependencies(callable|Closure|string $callback, array $params = []): array
    {
        $reflection = match (true) {
            is_array($callback) => new ReflectionMethod($callback[0], $callback[1]),
            is_object($callback) && !$callback instanceof Closure => new ReflectionMethod($callback, '__invoke'),
            default => new ReflectionFunction($callback),
        };

        $args = [];

        foreach ($reflection->getParameters() as $key => $reflectionParameter) {
            /** @var ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType $type */
            $type = $reflectionParameter->getType();
            $name = $reflectionParameter->getName();
            $className = $this->getClassName($type);

            if ($reflectionParameter->isVariadic()) {
                $variadicArgs = [];

                while (count($params)) {
                    $variadicArgs[] = $this->resolveBuiltInType($reflectionParameter, $name, $params);
                }

                $args = [...$args, ...$variadicArgs];
            } else {
                $args[] = $this->resolveDependency($reflectionParameter, $className, $name, $key, $params);
            }
        }

        return $args;
    }

    /**
     * Resolves dependencies by replacing them with the actual object instances.
     *
     * @param array $dependencies The dependencies.
     * @param ReflectionClass|null $reflection The class reflection associated with the dependencies.
     *
     * @throws Exception If a dependency cannot be resolved.
     * @throws InvalidConfig If a dependency cannot be resolved, or if a dependency cannot be fulfilled.
     * @throws NotInstantiable If a dependency cannot be resolved, or if a dependency cannot be fulfilled.
     * Missing required parameter when instantiating a class.
     * @throws Throwable In case of circular references.
     *
     * @return array The resolved dependencies.
     */
    public function resolveDependencies(array $dependencies, ReflectionClass|null $reflection = null): array
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                try {
                    $dependencies[$index] = $this->container->get($dependency->id);
                } catch (Exception | Throwable $e) {
                    if ($reflection !== null) {
                        $parameter = $reflection->getConstructor()?->getParameters()[$index] ?? null;

                        if ($parameter?->isOptional()) {
                            $dependencies[$index] = $parameter->getDefaultValue();
                            continue;
                        }

                        $name = $parameter->getName();
                        $class = $reflection->getName();

                        throw new NotInstantiable(
                            Message::PARAMETER_MISSING->getMessage($name, $class),
                        );
                    }

                    throw $e;
                }
            } elseif ($this->resolveArrays && is_array($dependency)) {
                $dependencies[$index] = $this->resolveDependencies($dependency, $reflection);
            }
        }

        return $dependencies;
    }

    /**
     * Extracts the class name from a reflection type.
     *
     * @param ReflectionIntersectionType|ReflectionNamedType|ReflectionType|null $reflectionType The reflection type.
     *
     * @return string|null The extracted class name, or `null` if no class name is found.
     */
    private function getClassName(
        ReflectionIntersectionType|ReflectionNamedType|ReflectionType|null $reflectionType,
    ): string|null {
        if ($reflectionType instanceof ReflectionNamedType && $reflectionType->isBuiltin() === false) {
            return $reflectionType->getName();
        }

        if ($reflectionType instanceof ReflectionUnionType || $reflectionType instanceof ReflectionIntersectionType) {
            /** @phpstan-var ReflectionNamedType[] $types */
            $types = $reflectionType->getTypes();

            foreach ($types as $type) {
                if ($type->isBuiltin() === false) {
                    return $type->getName();
                }
            }
        }

        return null;
    }

    /**
     * Returns the dependencies of the specified class.
     *
     * @param string $class The class name, interface name or alias name.
     *
     * @throws NotInstantiable if a dependency cannot be resolved, or if a dependency cannot be fulfilled.
     *
     * @return array the dependencies of the specified class.
     */
    private function getDependencies(string $class): array
    {
        if (isset($this->reflections[$class])) {
            return [$this->reflections[$class], $this->dependencies[$class]];
        }

        $dependencies = [];

        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new NotInstantiable(Message::INSTANTIATION_FAILED->getMessage($class));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isVariadic() === false) {
                    $type = $param->getType();

                    if ($type instanceof ReflectionNamedType && $type->isBuiltin() === false) {
                        $dependencies[$param->getName()] = Instance::of($type->getName());
                    } elseif ($param->isDefaultValueAvailable()) {
                        $dependencies[$param->getName()] = $param->getDefaultValue();
                    }
                }
            }
        }

        $this->reflections[$class] = $reflection;
        $this->dependencies[$class] = $dependencies;

        return [$reflection, $dependencies];
    }

    /**
     * Returns a ReflectionClass object for the specified class.
     *
     * @param string $class The class name.
     *
     * @return ReflectionClass The ReflectionClass object for the specified class.
     */
    private function getReflection(string $class): ReflectionClass
    {
        return $this->reflections[$class] ?? new ReflectionClass($class);
    }

    /**
     * Merges two arrays into one.
     *
     * @param array $a The array to be merged to.
     * @param array $b The array to be merged from.
     *
     * @return array The merged array (the original arrays are not changed.)
     */
    private function mergeDependencies(array $a, array $b): array
    {
        if ($b !== [] && is_int(key($b))) {
            return array_replace(array_values($a), $b);
        }

        $merged = array_replace($a, $b);

        return array_values($merged);
    }

    /**
     * Resolves dependencies for built-in type parameters.
     *
     * @param ReflectionParameter $reflectionParameter The parameter being resolved.
     * @param string $name The name of the parameter.
     * @param array $params Reference to the parameters array.
     *
     * @throws InvalidConfig If a required parameter cannot be resolved.
     *
     * @return mixed The resolved parameter value.
     */
    private function resolveBuiltInType(ReflectionParameter $reflectionParameter, string $name, array &$params): mixed
    {
        if (array_key_exists($name, $params)) {
            $value = $params[$name];

            unset($params[$name]);

            return $value;
        }

        if (count($params)) {
            return array_shift($params);
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        throw new InvalidConfig(
            Message::PARAMETER_CALLABLE_MISSING->getMessage(
                $name,
                $reflectionParameter->getDeclaringFunction()->getName(),
            ),
        );
    }

    /**
     * Resolves a single dependency for a method or function parameter.
     *
     * @param ReflectionParameter $reflectionParameter The parameter being resolved.
     * @param string|null $className The class name of the parameter type.
     * @param string $name The name of the parameter.
     * @param string|int $key The key of the parameter in the parameters array.
     * @param array $params Reference to the parameters array.
     *
     * @throws InvalidConfig If a required dependency cannot be resolved.
     *
     * @return mixed The resolved dependency.
     */
    private function resolveDependency(
        ReflectionParameter $reflectionParameter,
        string|null $className,
        string $name,
        string|int $key,
        array &$params,
    ): mixed {
        if ($className === null) {
            return $this->resolveBuiltInType($reflectionParameter, $name, $params);
        }

        if (array_key_exists($name, $params)) {
            $value = $params[$name];

            unset($params[$name]);

            return $value;
        }

        if (array_key_exists(0, $params) && $params[0] instanceof $className) {
            return array_shift($params);
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        unset($params[$key]);

        return $this->container->get($className);
    }

    /**
     * Validates the format of dependencies passed during object creation.
     *
     * @param array $parameters The parameters to validate.
     *
     * @throws InvalidConfig If dependencies are not in the correct format.
     */
    private function validateDependencies(array $parameters): void
    {
        if (Arr::isList($parameters)) {
            return;
        }

        if (Arr::isAssociative($parameters)) {
            return;
        }

        throw new InvalidConfig(Message::DEPENDENCIES_IDX_NAME_POSITION->getMessage());
    }
}

<?php

/**
 * @file
 * Contains Magnum\Container\Compiler\FullAutowirePass
 */

namespace Magnum\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Implements a full auto-wire pass in the Symfony DI Compiler
 *
 * @package Magnum\Container\Compiler
 */
class FullAutowirePass
	extends AbstractRecursivePass
{
	/**
	 * @var array Caches the class arguments/parameters
	 */
	protected $classArgumentParameterCache = [];

	/**
	 * @var ResolveDefaultParameters
	 */
	protected $defaultParameters;

	/**
	 * @var array List of arguments that could be nullable
	 */
	protected $nullables = [];

	public function __construct(ResolveDefaultParameters $defaultParameters)
	{
		$this->defaultParameters = $defaultParameters;
	}

	public function process(ContainerBuilder $container)
	{
		parent::process($container);

		foreach ($this->nullables as $nullable) {
			/** @var \ReflectionParameter $param */
			list ($definition, $type, $key, $param) = $nullable;
			if ($container->hasDefinition($type) === false) {
				$definition->setArgument($key, $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
			}
		}
	}

	/**
	 * @param mixed $value
	 * @param bool  $isRoot
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected function processValue($value, $isRoot = false)
	{
		if ($value instanceof Definition) {
			if ($value->isAutowired() && $value->getClass()) {
				$this->resolveAutowiredReference($value);
			}
			elseif ($value->getFactory()) {
				try {
					$this->resolveFactoryReferences($value);
				}
				catch (\Exception $e) {
					throw $e;
				}
			}
		}

		return parent::processValue($value, $isRoot);
	}

	/**
	 * Runs the auto-wire resolution on the factory method
	 *
	 * @param Definition $definition
	 * @throws \ReflectionException
	 */
	protected function resolveFactoryReferences(Definition $definition)
	{
		list($class, $method) = $definition->getFactory();
		if ($reflectionClass = $this->container->getReflectionClass($class, false)) {
			if ($reflectionMethod = $reflectionClass->getMethod($method)) {
				$params = $reflectionMethod->getParameters();
				$args   = $definition->getArguments();
				foreach ($params as $param) {
					$name = '$' . $param->getName();
					if (array_key_exists($name, $args) || array_key_exists($param->getPosition(), $args)) {
						continue;
					}

					if ($type = ProxyHelper::getTypeHint($reflectionMethod, $param, true)) {
						$this->load($type);
						$definition->setArgument($param->getPosition(), new Reference($type));
					}
				}
			}
		}
	}

	/**
	 * Runs the auto-wire resolution if the constructor needs it
	 *
	 * @param Definition $value
	 * @throws \ReflectionException
	 */
	protected function resolveAutowiredReference(Definition $value): void
	{
		$class = $value->getClass();
		if ($reflectionClass = $this->container->getReflectionClass($class, false)) {
			try {
				$constructor = $this->getConstructor($value, false);
			}
			catch (\RuntimeException $e) {
				throw new AutowiringFailedException($this->currentId, $e->getMessage(), 0, $e);
			}

			if ($constructor) {
				$this->loadConstructorArguments($value, $constructor, $value->getArguments());
			}
		}
		else {
			$this->container->log(
				$this,
				sprintf(
					'Skipping service "%s": Class or interface "%s" cannot be loaded.',
					$this->currentId,
					$class
				)
			);
		}
	}

	/**
	 * Loads any types defined in the constructor
	 *
	 * @param \ReflectionMethod|\ReflectionFunction $constructor The Constructor reflector
	 * @param array                                 $arguments   List of arguments
	 * @throws \ReflectionException
	 */
	protected function loadConstructorArguments($definition, $constructor, array $arguments = [])
	{
		$parameters = $constructor->getParameters();
		if ($constructor->isVariadic()) {
			array_pop($parameters);
		}

		foreach ($parameters as $idx => $param) {
			unset($value);
			$key = '$' . $param->getName();
			if ((array_key_exists($idx, $arguments) && '' !== $arguments[$idx]) ||
				isset($arguments[$key]) ||
				isset($arguments[$idx])
			) {
				continue;
			}

			if ($type = ProxyHelper::getTypeHint($constructor, $param, true)) {
				if ($this->load($type) === false) {
					if ($param->allowsNull()) {
						$definition->setArgument($key, null);
					}
					if ($type === 'App\Http\Action\PhpInput') {
						var_dump($definition);
						die;
					}
				}
				else {
					$definition->setArgument($key, new Reference($type));
				}
			}
			else {
				$parentClass = $constructor->getDeclaringClass()->getParentClass();
				if ($parentClass === false) {
					$parentClass = $this->container->getReflectionClass($definition->getClass(), false)
												   ->getParentClass();
				}

				if ($value = $this->resolveInheritedValue($parentClass, $key) ?: $this->defaultParameters->get($key)) {
					// there was no type hint: List as missing and any parent classes will be checked
					$definition->setArgument($key, $value);
				}
			}
		}
	}

	/**
	 * Attempts to resolve the value by checking the inherited classes
	 *
	 * @param \ReflectionClass|bool $class
	 * @param string                $key
	 * @return bool|Reference|null
	 * @throws \ReflectionException
	 */
	protected function resolveInheritedValue($class, string $key)
	{
		if (false === $class) {
			return false;
		}

		list($arguments, $params) = $this->resolveConstructorArgumentsAndParameters($class->getName());

		if (isset($arguments[$key])) {
			return $arguments[$key];
		}

		if (isset($params[$key])) {
			$this->load($params[$key]);

			return new Reference($params[$key]);
		}

		return null;
	}

	/**
	 * Resolves the given classes arguments and parameters
	 *
	 * This caches the information as it might be used multiple times during compilation
	 *
	 * @param string $class
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function resolveConstructorArgumentsAndParameters($class)
	{
		if (isset($this->classArgumentParameterCache[$class])) {
			return $this->classArgumentParameterCache[$class];
		}

		$parentParams = [];
		$arguments    = [];
		if ($this->container->hasDefinition($class)) {
			$parent      = $this->container->getDefinition($class);
			$constructor = $this->getConstructor($parent, false);
			$arguments   = $parent->getArguments();
		}
		else {
			// handle aliases
			if ($this->container->hasAlias($class)) {
				$class = (string)$this->container->getAlias($class);
				if ($this->container->hasDefinition($class) || $this->load($class)) {
					// the container has a definition or it loaded
					return $this->resolveConstructorArgumentsAndParameters($class);
				}
			}

			$constructor = $this->container->getReflectionClass($class, false)->getConstructor();
		}

		$constructor && array_map(
			function ($param) use (&$parentParams, &$constructor) {
				$key                = '$' . $param->getName();
				$parentParams[$key] = ProxyHelper::getTypeHint($constructor, $param, true);

				return $key;
			},
			$constructor->getParameters()
		);

		$this->classArgumentParameterCache[$class] = [
			$arguments,
			$parentParams
		];

		return $this->classArgumentParameterCache[$class];
	}

	/**
	 * Loads the class as an autowire
	 *
	 * @param $class
	 * @return bool Whether the class was loaded
	 * @throws \ReflectionException
	 */
	protected function load($class): bool
	{
		if (empty($class)) {
			return false;
		}

		if (
			$this->container->hasDefinition($class) ||
			$this->container->hasAlias($class) ||
			$this->container->has($class)

		) {
			return true;
		}

		if ($reflectionClass = $this->container->getReflectionClass($class, false)) {
			if ($reflectionClass->isInterface() || $reflectionClass->isAbstract() || $reflectionClass->isTrait()) {
				// do not try to autowire these
				return false;
			}

			// Leave these private to prevent abuse of the system.
			$definition = $this->container->autowire($class, $class);
			if ($constructor = $reflectionClass->getConstructor()) {
				$this->loadConstructorArguments($definition, $constructor);

				return true;
			}
		}

		return false;
	}
}
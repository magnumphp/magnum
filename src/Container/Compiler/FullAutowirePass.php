<?php

namespace Magnum\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper;

/**
 * Implements a full auto-wire pass in the Symfony DI Compiler
 *
 * @package Magnum\Container\Compiler
 */
class FullAutowirePass
	extends AbstractRecursivePass
{
	/**
	 * @param mixed $value
	 * @param bool  $isRoot
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected function processValue($value, $isRoot = false)
	{
		if ($value instanceof Definition &&
			$value->isAutowired() &&
			$value->getClass()
		) {
			$this->resolveAutowiredReference($value);
		}

		return parent::processValue($value, $isRoot);
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
				$this->loadConstructorArguments($constructor, $value->getArguments());
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
	 * @param \ReflectionMethod $constructor The Constructor reflector
	 * @param array             $arguments   List of arguments
	 * @throws \ReflectionException
	 */
	protected function loadConstructorArguments(\ReflectionMethod $constructor, array $arguments = [])
	{
		$parameters = $constructor->getParameters();
		if ($constructor->isVariadic()) {
			array_pop($parameters);
		}

		foreach ($parameters as $idx => $param) {
			$key = '$' . $param->getName();
			if ((array_key_exists($idx, $arguments) && '' !== $arguments[$idx]) ||
				isset($arguments[$key]) ||
				isset($arguments[$idx])
			) {
				continue;
			}

			if ($type = ProxyHelper::getTypeHint($constructor, $param, true)) {
				// check if the container already knows how to handle this type.
				if ($this->container->hasDefinition($type) ||
					$this->container->hasAlias($type) ||
					$this->container->has($type)
				) {
					continue;
				}

				// container doesn't know about it, load it
				if (class_exists($type)) {
					$this->load($type);
				}
			}
		}
	}

	/**
	 * Loads the class as an autowire
	 *
	 * @param $class
	 * @throws \ReflectionException
	 */
	protected function load($class): void
	{
		if ($reflectionClass = $this->container->getReflectionClass($class, false)) {
			if ($constructor = $reflectionClass->getConstructor()) {
				$this->loadConstructorArguments($constructor);

				// Leave these private to prevent abuse of the system.
				$this->container->autowire($class, $class);
			}
		}
	}
}
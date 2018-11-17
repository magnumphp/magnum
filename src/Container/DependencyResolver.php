<?php
declare(strict_types=1);

namespace Magnum\Container;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Magnum\Container\Config\EntryPoint;
use Magnum\Container\Param\Param;
use PhpDocReader\PhpDocReader;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use WoohooLabs\Zen\Config\AbstractCompilerConfig;
use WoohooLabs\Zen\Config\EntryPoint\EntryPointInterface;
use WoohooLabs\Zen\Config\Hint\DefinitionHintInterface;
use WoohooLabs\Zen\Container\Definition\ClassDefinition;
use WoohooLabs\Zen\Container\Definition\DefinitionInterface;
use WoohooLabs\Zen\Container\Definition\ReferenceDefinition;
use WoohooLabs\Zen\Container\Definition\SelfDefinition;
use WoohooLabs\Zen\Exception\ContainerException;

/**
 * Resolves dependencies.
 *
 * This is the full DependencyResolver from Woohoo Labs
 *
 * Extra Features:
 *  - ClassEntryPoint can have constructor params applied to it and they will be used.
 *  - EntryPoints can "define" their own definitions and they will be used.
 *
 * @copyright 2018, Woohoo Labs.
 * @license   MIT
 */
class DependencyResolver
{
	/**
	 * @var AbstractCompilerConfig
	 */
	protected $compilerConfig;

	/**
	 * @var DefinitionHintInterface[]
	 */
	protected $definitionHints;

	/**
	 * @var DefinitionInterface[]
	 */
	protected $definitions;

	/**
	 * @var SimpleAnnotationReader
	 */
	protected $annotationReader;

	/**
	 * @var PhpDocReader
	 */
	protected $typeHintReader;

	/**
	 * @var array
	 */
	protected $deferred = [];

	public function __construct(CompilerConfig $compilerConfig)
	{
		$definitionHints = [];
		foreach ($compilerConfig->getContainerConfigs() as $containerConfig) {
			$definitionHints = array_merge($definitionHints, $containerConfig->createDefinitionHints());
		}

		$this->compilerConfig  = $compilerConfig;
		$this->definitionHints = $definitionHints;
		if ($this->compilerConfig->useAnnotations()) {
			$this->setAnnotationReader();
		}
		$this->typeHintReader = new PhpDocReader();

		$this->definitions = [
			$this->compilerConfig->getContainerFqcn() => new SelfDefinition($this->compilerConfig->getContainerFqcn()),
			ContainerInterface::class                 => new ReferenceDefinition(
				ContainerInterface::class,
				$this->compilerConfig->getContainerFqcn()
			),
		];
	}

	/**
	 * Resolves the entry points from the container configurations
	 */
	public function resolveEntryPoints(): void
	{
		foreach ($this->compilerConfig->getContainerConfigs() as $containerConfig) {
			foreach ($containerConfig->createEntryPoints() as $entryPoint) {
				foreach ($entryPoint->getClassNames() as $id) {
					try {
						if (isset($this->definitions[$id])) {
							unset($this->definitions[$id]);
						}

						$this->resolve($id, $entryPoint);
						if (isset($this->deferred[$id])) {
							unset($this->deferred[$id]);
						}
					}
					catch (ContainerException $e) {
						$this->deferred[$id] = $entryPoint;
					}
				}
			}
		}

		foreach ($this->deferred as $id => $entryPoint) {
			$this->resolve($id, $entryPoint);
		}
	}

	/**
	 * @return DefinitionInterface[] The list of definitions
	 */
	public function getDefinitions(): array
	{
		return $this->definitions;
	}

	/**
	 * Resolves the id in to it's definition
	 *
	 * @param string              $id         The ID to resolve
	 * @param EntryPointInterface $entryPoint The EntryPoint to use for custom definitions or constructor params
	 * @throws ContainerException
	 * @throws \PhpDocReader\AnnotationException
	 */
	private function resolve(string $id, ?EntryPointInterface $entryPoint = null): void
	{
		if (isset($this->definitions[$id])) {
			if ($this->definitions[$id]->needsDependencyResolution()) {
				$this->resolveDependencies($id, $entryPoint);
			}

			return;
		}

		$isAutoloaded = false;
		if ($entryPoint && ($this->compilerConfig->getAutoloadConfig()
												 ->isGlobalAutoloadEnabled() || $entryPoint->isAutoloaded())) {
			$isAutoloaded = true;
		}

		if (in_array($entryPoint, $this->compilerConfig->getAutoloadConfig()->getAlwaysAutoloadedClasses(), true)) {
			$isAutoloaded = false;
		}

		if (in_array($entryPoint, $this->compilerConfig->getAutoloadConfig()->getExcludedClasses(), true)) {
			$isAutoloaded = false;
		}

		if (isset($this->definitionHints[$id])) {
			$definitions = $this->definitionHints[$id]->toDefinitions($this->definitionHints, $id, $isAutoloaded);
			foreach ($definitions as $definitionId => $definition) {
				/** @var DefinitionInterface $definition */
				if (isset($this->definitions[$definitionId]) === false) {
					$this->definitions[$definitionId] = $definition;
				}
				$this->resolve($definitionId);
			}

			return;
		}

		if ($entryPoint !== null && $entryPoint instanceof EntryPoint) {
			$this->definitions[$id] = $entryPoint->definition($id, $isAutoloaded);
		}
		else {
			$this->definitions[$id] = new ClassDefinition($id, "singleton", $isAutoloaded);
		}
		$this->resolveDependencies($id, $entryPoint);
	}

	/**
	 * Resolves the dependencies for the given ID and entry point
	 *
	 * Important note: Magnum does not support property injection
	 *
	 * @param string                   $id
	 * @param null|EntryPointInterface $entryPoint
	 * @throws ContainerException
	 * @throws \PhpDocReader\AnnotationException
	 */
	private function resolveDependencies(string $id, ?EntryPointInterface $entryPoint = null): void
	{
		$this->definitions[$id]->resolveDependencies();

		if ($this->definitions[$id] instanceof ClassDefinition) {
			if ($this->compilerConfig->useConstructorInjection()) {
				$this->resolveConstructorArguments($this->definitions[$id], $entryPoint);
			}
		}
		else {
			foreach ($this->definitions[$id]->getClassDependencies() as $name) {
				$this->resolve($name, $entryPoint);
			}
		}
	}

	/**
	 * Determines if the name exists as a class, Definition or DefinitionHint
	 *
	 * @param mixed $name
	 * @return bool True when the class exists or is a definition/definitionhint. False otherwise.
	 */
	protected function hasHintDefinitionOrClassExists($name)
	{
		if (is_object($name) || $name === null) {
			return false;
		}

		return class_exists($name) || isset($this->definitions[$name]) || isset($this->definitionHints[$name]);
	}

	/**
	 * Resolves the constructor arguments for the definition
	 *
	 * @param ClassDefinition          $definition
	 * @param null|EntryPointInterface $entryPoint
	 * @throws ContainerException
	 * @throws \PhpDocReader\AnnotationException
	 */
	private function resolveConstructorArguments(
		ClassDefinition $definition,
		?EntryPointInterface $entryPoint = null
	): void {
		try {
			$reflectionClass = new ReflectionClass($definition->getClassName());
		}
		catch (ReflectionException $e) {
			throw new ContainerException("Cannot inject class: " . $definition->getClassName());
		}

		if ($reflectionClass->getConstructor() === null) {
			return;
		}

		$useEntryPoint = isset($entryPoint) && $entryPoint instanceof EntryPoint;
		foreach ($reflectionClass->getConstructor()->getParameters() as $param) {
			$paramName = $param->getName();
			$value     = $useEntryPoint ? $entryPoint->getConstructorParam($paramName, null) : null;
			$isClass   = $this->hasHintDefinitionOrClassExists($value);

			if ($value instanceof Param || ($value !== null && !$isClass)) {
				$definition->addOptionalConstructorArgument($value);
				continue;
			}
			elseif ($param->isOptional() && !$isClass) {
				$definition->addOptionalConstructorArgument($param->getDefaultValue());
				continue;
			}

			$paramClass = ($value !== null && !is_object($value) && class_exists($value))
				? $value
				: $this->typeHintReader->getParameterClass($param);

			if ($paramClass === null) {
				if ($value !== null) {
					$definition->addOptionalConstructorArgument($value ?? $param->getDefaultValue());
					continue;
				}

				throw new ContainerException(
					"Type declaration or '@param' PHPDoc comment for constructor parameter '{$paramName}' in '" .
					"class '" . $definition->getClassName() . "' is missing or it is not a class!"
				);
			}

			$definition->addRequiredConstructorArgument($paramClass);
			$this->resolve($paramClass);
		}
	}

	/**
	 * Sets up the annotation reader
	 */
	private function setAnnotationReader(): void
	{
		$this->annotationReader = new SimpleAnnotationReader();
		$this->annotationReader->addNamespace('WoohooLabs\Zen\Annotation');
	}
}

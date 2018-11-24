<?php
declare(strict_types=1);

namespace Magnum\Container;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Magnum\Container\Config\ContextDependentParamHint;
use Magnum\Container\Config\EntryPoint;
use Magnum\Container\Config\ParamHint;
use Magnum\Container\Exception\MissingClass;
use Magnum\Container\Exception\MissingParameter;
use Magnum\Container\Param\Param;
use PhpDocReader\PhpDocReader;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use WoohooLabs\Zen\Config\AbstractCompilerConfig;
use WoohooLabs\Zen\Config\EntryPoint\EntryPointInterface;
use WoohooLabs\Zen\Config\Hint\DefinitionHintInterface;
use WoohooLabs\Zen\Container\Definition\ClassDefinition;
use WoohooLabs\Zen\Container\Definition\ContextDependentDefinition;
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

		$containerName     = $this->compilerConfig->getContainerFqcn();
		$this->definitions = [
			$containerName            => new SelfDefinition($containerName),
			ContainerInterface::class => new ReferenceDefinition(ContainerInterface::class, $containerName),
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
					if (!class_exists($id)) {
						throw new MissingClass($id);
					}

					try {
						if (isset($this->definitions[$id])) {
							unset($this->definitions[$id]);
						}

						$this->resolve($id, $entryPoint);
						if (isset($this->deferred[$id])) {
							unset($this->deferred[$id]);
						}
					}
					catch (MissingClass $e) {
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

		$isAutoloaded   = false;
		$autoloadConfig = $this->compilerConfig->getAutoloadConfig();
		if ($entryPoint && ($autoloadConfig->isGlobalAutoloadEnabled() || $entryPoint->isAutoloaded())) {
			$isAutoloaded = true;
		}

		if (in_array($entryPoint, $autoloadConfig->getAlwaysAutoloadedClasses(), true)) {
			$isAutoloaded = false;
		}

		if (in_array($entryPoint, $autoloadConfig->getExcludedClasses(), true)) {
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
	protected function resolveDependencies(string $id, ?EntryPointInterface $entryPoint = null): void
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
	 * Resolves the constructor arguments for the definition
	 *
	 * @param ClassDefinition          $definition
	 * @param null|EntryPointInterface $entryPoint
	 * @throws ContainerException
	 * @throws \PhpDocReader\AnnotationException
	 */
	protected function resolveConstructorArguments(
		ClassDefinition $definition,
		?EntryPointInterface $entryPoint = null
	): void {
		$className = $definition->getClassName();
		try {
			$reflectionClass = new ReflectionClass($className);
		}
		catch (ReflectionException $e) {
			throw new MissingClass($className);
		}

		if ($reflectionClass->getConstructor() === null) {
			return;
		}

		foreach ($reflectionClass->getConstructor()->getParameters() as $param) {
			$value = $this->resolveValue($className, $entryPoint, $param);
			if (is_string($value) && class_exists($value)) {
				$definition->addRequiredConstructorArgument($value);
				$this->resolve($value);
			}
			elseif ($value) {
					$definition->addOptionalConstructorArgument($value);
			}
			elseif ($param->isOptional()) {
				$definition->addOptionalConstructorArgument($value);
			}
			else {
				throw new MissingParameter($className, $param->getName());
			}
		}
	}

	/**
	 * Resolves the hint value based on the class and param it's looking for
	 *
	 * @param                      $className
	 * @param EntryPoint|null      $entryPoint
	 * @param \ReflectionParameter $param
	 * @return mixed
	 * @throws MissingParameter
	 * @throws \PhpDocReader\AnnotationException
	 */
	protected function resolveValue($className, ?EntryPoint $entryPoint = null, \ReflectionParameter $param)
	{
		$paramName = $param->getName();
		$value = $entryPoint ? $entryPoint->getConstructorParam($paramName) : null;
		if ($value) {
			return $value;
		}

		if (!isset($this->definitionHints[$key = "{$className}.{$paramName}"]) &&
			!isset($this->definitionHints[$key = $paramName])
		) {
			if ($param->isOptional()) {
				return $param->getDefaultValue();
			}
			else{
				$paramType = $this->typeHintReader->getParameterClass($param);
				if ($paramType) {
					return $paramType;
				}

				throw new MissingParameter($className, $paramName);
			}
		}

		$value = $this->definitionHints[$key];
		if ($value instanceof ContextDependentParamHint) {
			$value = $value->resolve($className);
		}
		elseif ($value instanceof ParamHint) {
			$value = $value->param();
		}

		return $value;
	}

	/**
	 * Sets up the annotation reader
	 */
	protected function setAnnotationReader(): void
	{
		$this->annotationReader = new SimpleAnnotationReader();
		$this->annotationReader->addNamespace('WoohooLabs\Zen\Annotation');
	}
}

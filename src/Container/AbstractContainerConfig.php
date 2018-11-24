<?php

namespace Magnum\Container;

use Magnum\Container\Config\ClassEntryPoint;
use Magnum\Container\Config\FactoryHint;
use Magnum\Container\Config\ParamHint;
use Magnum\Container\Param\Param;
use WoohooLabs\Zen\Config\ContainerConfigInterface;
use WoohooLabs\Zen\Config\EntryPoint\EntryPointInterface;
use WoohooLabs\Zen\Config\Hint\DefinitionHint;
use WoohooLabs\Zen\Config\Hint\DefinitionHintInterface;
use WoohooLabs\Zen\Exception\ContainerException;

/**
 * Simplified ContainerConfig definition
 *
 * @copyright 2018, Woohoo Labs.
 * @license   MIT
 */
abstract class AbstractContainerConfig
	implements ContainerConfigInterface
{
	use ProvidesContainerConfig;

	/**
	 * @var array List of entry points
	 */
	protected $entryPoints = [];

	/**
	 * @var array List of definition hints
	 */
	protected $definitionHints = [];

	/**
	 * @var array List of Wildcard hints
	 */
	protected $wildcardHints = [];

	/**
	 * @return EntryPointInterface[]
	 */
	public function createEntryPoints(): array
	{
		return array_map(
			function ($entryPoint): EntryPointInterface {
				if ($entryPoint instanceof EntryPointInterface) {
					return $entryPoint;
				}

				if (\is_string($entryPoint)) {
					return new ClassEntryPoint($entryPoint);
				}

				throw new ContainerException("An entry point must be either a string or an EntryPoint object!");
			},
			$this->getEntryPoints()
		);
	}

	/**
	 * @return DefinitionHintInterface[]
	 */
	public function createDefinitionHints(): array
	{
		$definitionHints = array_map(
			function ($definitionHint): DefinitionHintInterface {
				if ($definitionHint instanceof DefinitionHintInterface) {
					return $definitionHint;
				}

				if (\is_string($definitionHint)) {
					return new DefinitionHint($definitionHint);
				}

				if (\is_array($definitionHint)) {
					return new FactoryHint(...$definitionHint);
				}

				if ($definitionHint instanceof Param) {
					return new ParamHint($definitionHint);
				}

				throw new ContainerException("A definition hint must be either a string or a DefinitionHint object");
			},
			$this->definitionHints
		);

		foreach ($this->wildcardHints as $wildcardHint) {
			$definitionHints = array_merge($definitionHints, $wildcardHint->getDefinitionHints());
		}

		return $definitionHints;
	}
}
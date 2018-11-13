<?php

namespace Magnum\Container;

use Magnum\Container\Config\FactoryHint;
use WoohooLabs\Zen\Config\AbstractContainerConfig;
use WoohooLabs\Zen\Config\Hint\DefinitionHint;
use WoohooLabs\Zen\Config\Hint\DefinitionHintInterface;
use WoohooLabs\Zen\Exception\ContainerException;

/**
 * Simplified provider definition
 *
 * @package Magnum\Container
 */
class Provider
	extends AbstractContainerConfig
{
	use ProvidesContainerConfig;

	public function __construct($entryPoints = [], $definitionHints = [], $wildcardHints = [])
	{
		$this->entryPoints     = $entryPoints;
		$this->definitionHints = $definitionHints;
		$this->wildcardHints   = $wildcardHints;
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

				throw new ContainerException("A definition hint must be either a string or a DefinitionHint object");
			},
			$this->getDefinitionHints()
		);

		foreach ($this->getWildcardHints() as $wildcardHint) {
			$definitionHints = array_merge($definitionHints, $wildcardHint->getDefinitionHints());
		}

		return $definitionHints;
	}
}
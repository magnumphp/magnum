<?php

namespace Magnum\Container\Config;

use Magnum\Container\Definition\FactoryDefinition;
use WoohooLabs\Zen\Config\Hint\DefinitionHint;

/**
 * Hint for injecting a factory
 *
 * @package Magnum\Container\Config
 */
class FactoryHint
	extends DefinitionHint
{
	/**
	 * @var string The class to use
	 */
	protected $class;

	/**
	 * @var string The method to call
	 */
	protected $method;

	public function __construct($class, $method)
	{
		$this->class  = $class;
		$this->method = $method;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toDefinitions(array $definitionHints, string $id, bool $isAutoloaded): array
	{
		return [
			$id => new FactoryDefinition($id, $this->class, $this->method)
		];
	}
}
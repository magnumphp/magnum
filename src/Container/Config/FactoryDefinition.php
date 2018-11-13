<?php

namespace Magnum\Container\Config;

use WoohooLabs\Zen\Container\Definition\AbstractDefinition;
use WoohooLabs\Zen\Container\Definition\DefinitionInterface;

/**
 * Defines a factory in the constructor
 *
 * @package Magnum\Container\Config
 */
class FactoryDefinition
	extends AbstractDefinition
{
	/**
	 * @var string The class to use
	 */
	protected $class;

	/**
	 * @var string The method to calls
	 */
	protected $method;

	public function __construct(string $id, string $class, string $method)
	{
		parent::__construct($id, 'factory');
		$this->class = $class;
		$this->method = $method;
	}

	/**
	 * {@inheritdoc}
	 */
	public function needsDependencyResolution(): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAutoloaded(): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveDependencies(): DefinitionInterface
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClassDependencies(): array
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function toPhpCode(array $definitions): string
	{
		return <<<EOF
        return \\$this->class::$this->method();

EOF;
	}

}
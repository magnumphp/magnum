<?php

namespace Magnum\Container\Definition;

use Magnum\Container\Param\StaticMethodParam;
use WoohooLabs\Zen\Container\Definition\AbstractDefinition;
use WoohooLabs\Zen\Container\Definition\DefinitionInterface;

/**
 * Defines a factory for the container
 *
 * @package Magnum\Container\Definition
 */
class FactoryDefinition
	extends AbstractDefinition
{
	use OnlyGeneratesCode;

	/**
	 * @var StaticMethodParam
	 */
	protected $generator;

	public function __construct(string $id, string $class, string $method, ...$params)
	{
		parent::__construct($id, 'factory');

		$this->generator = new StaticMethodParam($class, $method, ...$params);
	}

	/**
	 * {@inheritdoc}
	 */
	public function toPhpCode(array $definitions): string
	{
		return <<<EOF
        return $this->generator;

EOF;
	}

}
<?php

namespace Magnum\Container\Config;

use Magnum\Container\Definition\ParamDefinition;
use Magnum\Container\Param\Param;
use WoohooLabs\Zen\Config\Hint\DefinitionHintInterface;

/**
 * Hint for injecting a parameter
 *
 * @package Magnum\Container\Config
 */

class ParamHint
	implements DefinitionHintInterface
{
	/**
	 * @var Param The parameter
	 */
	protected $param;

	public function __construct(Param $param)
	{
		$this->param = $param;
	}

	/**
	 * @return Param
	 */
	public function param(): Param
	{
		return $this->param;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toDefinitions(array $definitionHints, string $id, bool $isAutoloaded): array
	{
		return [
			$id => new ParamDefinition($this->param)
		];
	}
}
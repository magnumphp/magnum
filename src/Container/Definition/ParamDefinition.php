<?php

namespace Magnum\Container\Definition;

use Magnum\Container\Param\Param;
use WoohooLabs\Zen\Container\Definition\AbstractDefinition;

/**
 * Defines a Param for the container
 *
 * @package Magnum\Container\Definition
 */
class ParamDefinition
	extends AbstractDefinition
{
	use OnlyGeneratesCode;

	/**
	 * @var Param
	 */
	protected $param;

	public function __construct(Param $param)
	{
		$this->param = $param;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toPhpCode(array $definitions): string
	{
		return "return $this->param;\n";
	}
}
<?php

namespace Magnum\Container\Param;

/**
 * Renders a boolean value to when cast to string
 *
 * @package Magnum\Container\Param
 */
class BooleanParam
	implements Param
{
	/**
	 * @var bool
	 */
	protected $value;

	public function __construct(bool $value)
	{
		$this->value = $value;
	}

	public function __toString(): string
	{
		return $this->value === true ? 'true' : 'false';
	}
}
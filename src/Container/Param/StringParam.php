<?php

namespace Magnum\Container\Param;

/**
 * Renders a string when cast to string
 *
 * @package Magnum\Container\Param
 */
class StringParam
	implements Param
{
	/**
	 * @var string The value to render
	 */
	protected $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		return (string)var_export($this->value, true);
	}
}
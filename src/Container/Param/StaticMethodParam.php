<?php

namespace Magnum\Container\Param;

/**
 * Renders a static method call on a class
 *
 * @package Magnum\Container\Param
 */
class StaticMethodParam
	extends ClassMethodParam
{
	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		return "\\{$this->class}::{$this->method}(" . $this->paramsToString($this->params) . ")";
	}
}
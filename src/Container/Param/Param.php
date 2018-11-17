<?php

namespace Magnum\Container\Param;

/**
 * Allows custom parameter loading in to the container
 *
 * @package Magnum\Container
 */
interface Param
{
	/**
	 * Cast the parameter to a string
	 *
	 * @return string
	 */
	public function __toString(): string;
}
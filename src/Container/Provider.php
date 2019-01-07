<?php

/**
 * @file
 * Contains Magnum\Container\Provider
 */

namespace Magnum\Container;

/**
 * Provider Contract
 *
 * @package Magnum\Container
 */
interface Provider
{
	/**
	 * Registers classes & parameters with the container builder
	 *
	 * @param Builder $builder
	 * @return mixed
	 */
	public function register(Builder $builder);
}
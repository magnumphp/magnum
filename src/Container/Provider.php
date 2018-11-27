<?php

namespace Magnum\Container;

/**
 * Provider Contract
 *
 * @package Magnum\Container
 */
interface Provider
{
	public function register(Builder $builder);
}
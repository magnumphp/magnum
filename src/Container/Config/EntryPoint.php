<?php

namespace Magnum\Container\Config;

use WoohooLabs\Zen\Config\EntryPoint\EntryPointInterface;
use WoohooLabs\Zen\Container\Definition\DefinitionInterface;

interface EntryPoint
	extends EntryPointInterface
{
	/**
	 * Returns the given constructor param, or the default if it doesn't exist
	 *
	 * @param string     $paramName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function getConstructorParam(string $paramName, $default = null);

	/**
	 * Returns the definition for this entry point if a custom one is defined
	 *
	 * @param string $id
	 * @param bool   $isAutoloaded
	 * @param string $scope
	 * @return DefinitionInterface
	 */
	public function definition(string $id, bool $isAutoloaded, $scope = 'singleton'): DefinitionInterface;
}
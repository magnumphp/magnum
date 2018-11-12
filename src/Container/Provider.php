<?php

namespace Magnum\Container;

use WoohooLabs\Zen\Config\AbstractContainerConfig;

/**
 * Simplified provider definition
 *
 * @package Magnum\Container
 */
class Provider
	extends AbstractContainerConfig
{
	use ProvidesContainerConfig;

	public function __construct($entryPoints, $definitionHints = [], $wildcardHints = [])
	{
		$this->entryPoints     = $entryPoints;
		$this->definitionHints = $definitionHints;
		$this->wildcardHints   = $wildcardHints;
	}
}
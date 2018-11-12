<?php

namespace Magnum\Container;

/**
 * Provides the 3 functions that the Zen AbstractContainerConfig class requires
 *
 * @package Magnum\Container
 */
trait ProvidesContainerConfig
{
	protected function getEntryPoints(): array
	{
		return $this->entryPoints ?? [];
	}

	protected function getDefinitionHints(): array
	{
		return $this->definitionHints ?? [];
	}

	protected function getWildcardHints(): array
	{
		return $this->wildcardHints ?? [];
	}
}
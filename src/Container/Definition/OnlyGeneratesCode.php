<?php

namespace Magnum\Container\Definition;

use WoohooLabs\Zen\Container\Definition\DefinitionInterface;

/**
 * Convenience trait for DefinitionInterface defaults indicating the class only needs to implement toPhpCode()
 *
 * @package Magnum\Container\Definition
 */
trait OnlyGeneratesCode
{
	/**
	 * {@inheritdoc}
	 */
	public function needsDependencyResolution(): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAutoloaded(): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveDependencies(): DefinitionInterface
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClassDependencies(): array
	{
		return [];
	}
}
<?php
declare(strict_types=1);

namespace Magnum\Container\Config;

use WoohooLabs\Zen\Container\Definition\ClassDefinition;
use WoohooLabs\Zen\Container\Definition\DefinitionInterface;

/**
 * @copyright 2018, Woohoo Labs.
 * @license MIT
 */

class ClassEntryPoint
	implements EntryPoint
{
	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var bool
	 */
	protected $autoloaded;

	/**
	 * @var array
	 */
	protected $constructorParams;

	public static function create(string $className, array $constructorParams = []): ClassEntryPoint
	{
		return new ClassEntryPoint($className, $constructorParams);
	}

	public function __construct(string $className, array $constructorParams = [])
	{
		$this->className         = $className;
		$this->constructorParams = $constructorParams;
		$this->autoloaded        = false;
	}

	/**
	 * Sets whether or not the class is autoloaded
	 *
	 * @return ClassEntryPoint
	 */
	public function autoload(): ClassEntryPoint
	{
		$this->autoloaded = true;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getClassNames(): array
	{
		return [
			$this->className,
		];
	}

	/**
	 * @return bool Whether or not the class is autoloaded
	 */
	public function isAutoloaded(): bool
	{
		return $this->autoloaded;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConstructorParam(string $paramName, $default = null)
	{
		return $this->constructorParams[$paramName] ?? $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function definition(string $id, bool $isAutoloaded, $scope = 'singleton'): DefinitionInterface
	{
		return new ClassDefinition($id, $scope, $isAutoloaded);
	}
}

<?php

namespace Magnum\Container\Param;

/**
 * Renders a method call on a class within the container
 *
 * @package Magnum\Container\Param
 */
class ClassMethodParam
	implements Param
{
	use ConvertsParamsToString;

	/**
	 * @var string The class to use the method on
	 */
	protected $class;

	/**
	 * @var string The method to call
	 */
	protected $method;

	/**
	 * @var array Parameters for the method
	 */
	protected $params;

	public function __construct($class, $method, ...$params)
	{
		$this->class  = $class;
		$this->method = $method;
		$this->params = $params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		$class  = $this->toSingleton($this->class);

		return "({$class})->{$this->method}(" . $this->paramsToString($this->params) . ")";
	}

	/**
	 * Converts the given id in to a singleton entry for the container
	 *
	 * @param $id
	 * @return string
	 */
	protected function toSingleton($id)
	{
		$hash = str_replace('\\', '__', $id);

		return "\$this->singletonEntries['{$id}'] ?? \$this->{$hash}()";
	}

}
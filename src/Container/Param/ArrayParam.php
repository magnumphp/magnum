<?php

namespace Magnum\Container\Param;

/**
 * Renders an array when cast to string
 *
 * @package Magnum\Container\Param
 */
class ArrayParam
	implements Param
{
	/**
	 * @var array The array parameters
	 */
	protected $params;

	public function __construct(...$params)
	{
		$this->params = $params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		$params = [];
		foreach ($this->params as $param) {
			$params[] = $param === null ? 'null' : (string)$param;
		}

		return 'array(' . join(', ', $params) . ')';
	}
}
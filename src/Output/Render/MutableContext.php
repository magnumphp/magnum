<?php

/**
 * @file
 * Contains Magnum\Output\Render\MutableContext
 */

namespace Magnum\Output\Render;

use Interop\Output\Context;

/**
 * Extends the Context to have extra options
 *
 * @package Magnum\Output\Render
 */
class MutableContext
	implements Context
{
	/**
	 * List of data for this context
	 *
	 * @var array
	 */
	private $data;

	/**
	 * @param string     $name The name of the var to retrieve from the context
	 * @param mixed|null $alt  The value if the var does not exist in this context
	 * @return mixed
	 */
	public function get($name, $alt = null)
	{
		return $this->data[$name] ?? $alt;
	}

	/**
	 * @param string $name  The name of the var to set on the context
	 * @param mixed  $value The value of the var
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	/**
	 * Always returns true
	 *
	 * {@inheritdoc}
	 */
	public function accepts($template)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function provide($template)
	{
		return $this->data;
	}
}
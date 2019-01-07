<?php

/**
 * @file
 * Contains Magnum\Output\Render\MutableContext
 */

namespace Magnum\Output\Render;

use Interop\Output\Context;

class MutableContext
	implements Context
{
	/**
	 * List of data for this context
	 *
	 * @var array
	 */
	private $data;

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
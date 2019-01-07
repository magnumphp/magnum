<?php

/**
 * @file
 * Contains Magnum\Output\Render\ViewContext
 */

namespace Magnum\Output\Render;

use Interop\Output\Context;

/**
 * Replaces the global context on the Engine.
 *
 * This is typically used by templates in the $view->var or $view['var'] style to allow raw access to variables. This
 * maintains a depth of levels but allows the child views to reference variables from higher up if they aren't overidden
 *
 * This is designed as both a caching and non-caching context and is not individually mutable
 */
class ViewContext
	extends \Phrender\Context\Collection
	implements \ArrayAccess
{
	/**
	 * @var int The depth of contexts that we have
	 */
	private $depth;

	/**
	 * @var array The data for this level
	 */
	private $data = [];

	/**
	 * Returns the context data for the current depth
	 *
	 * {@inheritdoc}
	 */
	public function provide($template)
	{
		$data = parent::provide($template);

		$this->data[$this->depth] = $data;

		return $data;
	}

	/**
	 * Add a context and increase the depth
	 *
	 * @param Context $context
	 */
	public function add(Context $context)
	{
		$this->depth++;
		parent::add($context);
	}

	/**
	 * Remove the given context and the depth
	 *
	 * @param Context $context
	 */
	public function remove(Context $context)
	{
		if (isset($this->data[$this->depth])) {
			unset($this->data[$this->depth]);
		}
		$this->depth--;

		parent::remove($context);
	}

	/**
	 * Allows direct access to the offset via object notation
	 *
	 * @magic
	 * @param $name
	 * @return mixed|null
	 */
	public function __get($name)
	{
		return $this->offsetGet($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		$depth = $this->depth;
		do {
			if (isset($this->data[$depth][$offset])) {
				return true;
			}
		} while ($depth--);

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		$depth = $this->depth;
		do {
			if (isset($this->data[$depth][$offset])) {
				return $this->data[$depth][$offset];
			}
		} while ($depth--);

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		// do nothing
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		// do nothing
	}
}
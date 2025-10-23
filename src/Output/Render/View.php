<?php

/**
 * @file
 * Contains Magnum\Output\Render\View
 */

namespace Magnum\Output\Render;

use Interop\Output\Context as InteropContext;
use Phrender\Template\Factory;
use Phrender\Template\Template;

class View
	extends Template
{
	/**
	 * NB: this should probably be protected and set via the factory
	 */
	public $escaper;

	public function __construct($file, Factory $factory)
	{
		if (empty($file)) {
			throw new \InvalidArgumentException("A file is required");
		}

		parent::__construct($file, $factory);
	}

	/**
	 * @param string $name
	 * @return mixed The variable
	 */
	public function __get($name)
	{
		$var = parent::__get($name);

		return is_string($var) ? $this->escape($var) : $var;
	}

	/**
	 * Escape the value
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function escape($value)
	{
		return isset($this->escaper)
			? $this->escaper->escape($value)
			: $value;
	}

	/**
	 * Returns the raw value
	 *
	 * @param $name
	 * @return mixed|null
	 */
	public function raw($name)
	{
		return parent::__get($name);
	}

	/**
	 * @param InteropContext $context
	 * @return string The rendered output
	 */
	public function render(InteropContext $context): string
	{
		$this->data = $context->provide($file = $this->name());

		if ($this->data) {
			// we need to map the escaper
			extract(array_map([$this, 'escape'], $this->data));
		}

		ob_start();

		require $file;

		$content = ob_get_clean();

		return $content === false ? '' : $content;
	}
}
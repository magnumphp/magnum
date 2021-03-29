<?php

/**
 * @file
 * Contains Magnum\Output\Render\View
 */

namespace Magnum\Output\Render;

use Interop\Output\Context as InteropContext;
use Interop\Output\TemplateFactory;
use Phrender\Template\Template;

class View
	extends Template
{
	public function __construct($file, TemplateFactory $factory)
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
	public function render(InteropContext $context)
	{
		$this->data = $context->provide($this->file());

		if ($this->data) {
			// we need to map the escaper
			extract(array_map([$this, 'escape'], $this->data));
		}

		ob_start();

		require $this->file();

		$content = ob_get_clean();

		return $content === false ? '' : $content;
	}
}
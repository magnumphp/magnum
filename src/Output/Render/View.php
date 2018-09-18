<?php

namespace Magnum\Output\Render;

use Interop\Output\Context as InteropContext;
use Interop\Output\Context;
use Interop\Output\TemplateFactory;
use Phrender\Engine;
use Phrender\Template\Template;

class View
	extends Template
{
	/**
	 * @var Engine
	 */
	protected $engine;

	public function __construct($file, TemplateFactory $factory, Engine $engine)
	{
		$this->engine   = $engine;
		parent::__construct($file, $factory);
	}

	/**
	 * @param string $layout Changes the layout
	 */
	public function changeLayout($layout)
	{
		$this->engine->changeLayout($layout);
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
	 * Determines if there are any usages of <?= $var ?> in the template.
	 *
	 * @return false|int
	 */
	protected function needsExtraction()
	{
		$data = file_get_contents($this->file);

		return preg_match('/<\?= \$(?!this)([^\?]+) \?>/', $data);
	}

	/**
	 * @param InteropContext $context
	 * @return string The rendered output
	 */
	public function render(InteropContext $context)
	{
		$this->data = $context->provide($this->file);

		if ($this->data) {
			// we need to map the escaper
			extract(array_map([$this, 'escape'], $this->data));
		}

		// regex check for <\?= $name \?\> so that we can actually auto escape quickly...

		ob_start();

		require $this->file;

		$content = ob_get_clean();

		return $content === false ? '' : $content;
	}

	/**
	 * Returns the raw value
	 *
	 * @param $name
	 * @return mixed|null
	 */
	protected function raw($name)
	{
		return parent::__get($name);
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
}
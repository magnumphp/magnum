<?php

/**
 * @file
 * Contains Magnum\Output\Render\TwoStage
 */

namespace Magnum\Output\Render;

use Phrender\Engine;

class TwoStage
	extends Engine
{
	/**
	 * @var int the level of rendering we are currently at
	 */
	private $depth = 0;

	/**
	 * @var string the name of the layout to use
	 */
	protected $layout = 'default';

	/**
	 * @var bool Whether or not the layout has been rendered already
	 */
	protected $layoutDone = false;

	/**
	 * Changes the layout in use
	 *
	 * @param string $layout
	 * @return $this
	 */
	public function changeLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Really only used for error pages to break out
	 */
	public function reset()
	{
		$this->depth = 0;
		unset($this->layoutDone);
	}

	/**
	 * Overrides the parent render to handle layouts
	 *
	 * @param      $template
	 * @param null $data
	 * @return string
	 */
	public function render($template, $data = null)
	{
		$this->depth++;

		$view   = $template{0} === '/' ? $template : "views/{$template}";
		$output = parent::render($view, $data);

		if (isset($this->layout) && --$this->depth === 0 && !isset($this->layoutDone)) {
			$layout = $this->layout{0} === '/' ? $this->layout : "layouts/{$this->layout}";

			$this->layoutDone = true;

			return parent::render($layout, array_merge($data, ['content' => $output]));
		}

		return $output;
	}

	/**
	 * Loads the requested template
	 *
	 * @param $template
	 * @return \Interop\Output\Template
	 */
	protected function loadTemplate($template)
	{
		return $this->factory->load($template, $this);
	}
}
<?php

/**
 * @file
 * Contains Magnum\Output\Render\TwoStage
 */

namespace Magnum\Output\Render;

use Phrender\Engine;

/**
 * Extends the Phrender\Engine to handle two-stage rendering
 *
 * This renders the given view into a layout, if the current view render depth is 0.
 *
 * @package Magnum\Output\Render
 */
class TwoStage
	extends Engine
	implements LayoutAware
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
		$this->layoutDone = false;
	}

	/**
	 * Overrides the parent render to handle layouts
	 *
	 * @param      $template
	 * @param null $data
	 * @return string
	 */
	public function render($template, $data = null): string
	{
		$this->depth++;

		$output = parent::render($this->asViewPath($template), $data);

		if (isset($this->layout) && --$this->depth === 0 && $this->layoutDone === false) {
			$this->layoutDone = true;

			return parent::render(
				$this->asLayoutPath($this->layout),
				array_merge($data, ['content' => $output])
			);
		}

		return $output;
	}

	/**
	 * @param string $layout The layout to locate under layouts
	 * @return string The path to the layout
	 */
	protected function asLayoutPath($layout)
	{
		return $layout[0] === '/' ? $layout : "layouts/{$layout}";
	}

	/**
	 * @param string $template The template to locate under views
	 * @return string The path to the template under views
	 */
	protected function asViewPath($template)
	{
		return $template[0] === '/' ? $template : "views/{$template}";
	}

	/**
	 * Loads the requested template
	 *
	 * @param $template
	 * @return \Interop\Output\Template
	 */
	protected function loadTemplate($template): \Interop\Output\Template
	{
		return $this->factory->load($template, $this);
	}
}
<?php

/**
 * @file
 * Contains Magnum\Output\Render\LayoutAware
 */

namespace Magnum\Output\Render;

/**
 * Contract for allowing an Engine to understand that it's layout stage may be changed
 *
 * @package Magnum\Output\Render
 */
interface LayoutAware
{
	/**
	 * Changes the layout
	 *
	 * @param string $layout
	 *
	 * @return mixed
	 */
	public function changeLayout(string $layout);
}
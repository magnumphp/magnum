<?php

/**
 * @file
 * Contains Magnum\Output\Render\TemplateFactory
 */

namespace Magnum\Output\Render;

use Interop\Output\Template;
use Phrender\Exception\TemplateNotFound;
use Phrender\Template\Factory;
use Psr\Container\ContainerInterface;

class TemplateFactory
	extends Factory
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * {@inheritdoc}
	 */
	public function has($template)
	{
		try {
			$this->load($template);

			return true;
		}
		catch (TemplateNotFound $e) {
			// nothing to do
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($template)
	{
		if ($template[0] === '/' && file_exists($template)) {
			return $this->create($template);
		}

		return parent::load($template);
	}

	/**
	 * Overrides the Factory method to provide our custom View
	 *
	 * {@inheritdoc}
	 */
	public function create($file): Template
	{
		return new View($file, $this);
	}
}
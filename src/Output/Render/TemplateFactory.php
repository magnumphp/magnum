<?php

namespace Magnum\Output\Render;

use Interop\Output\Template;
use Phrender\Engine;
use Phrender\Template\Factory;
use Psr\Container\ContainerInterface;

class TemplateFactory
	extends Factory
{
	/**
	 * @var Engine
	 */
	protected $engine;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	public function __construct(ContainerInterface $container, array $paths = [], string $ext = self::DEFAULT_EXT)
	{
		$this->container = $container;
		parent::__construct($paths, $ext);
	}

	public function create($file): Template
	{
		return new View($file, $this, $this->getEngine());
	}

	protected function getEngine(): Engine
	{
		if (!$this->engine) {
			$this->engine = $this->container->get(Engine::class);
		}

		return $this->engine;
	}

}
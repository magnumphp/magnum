<?php

/**
 * @file
 * Contains Magnum\Container\CompiledServiceContainer
 */

namespace Magnum\Container;

use Magnum\ProxyManager\Manager as ProxyManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Base class for compiled service containers.
 *
 * This loads the Magnum Proxy Manager so that the registered proxies are loaded without developer intervention
 *
 * @package Magnum\Container
 */
class CompiledServiceContainer
	extends Container
{
	/**
	 * {@inheritDoc}
	 */
	public function __construct(ParameterBagInterface $parameterBag = null)
	{
		parent::__construct($parameterBag);

		$this->init();

		$this->get(ProxyManager::class);
	}
}
<?php

/**
 * @file
 * Contains Magnum\Http\ServiceProvider
 */

namespace Magnum\Http;

use Magnum\Container\Builder;
use Magnum\Container\Provider;
use Magnum\Http\Container\MiddlewarePass;
use Magnum\Http\Message\ServerRequest\Factory;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Turbo\Provider\RouteProvider;
use Slim\Turbo\Provider\Symfony;
use Slim\Turbo\Routing\Cache\Memory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * Sets up the container with the appropriate data
 *
 * @package Magnum\Http
 */
class ServiceProvider
	implements Provider
{
	/**
	 * @var string The target class for the application
	 */
	protected $applicationTargetClass = Application::class;

	/**
	 * @var string|null The class that should be used in place of the target class
	 */
	protected $applicationActualClass = null;

	public function register(Builder $builder)
	{
		$directBuilder = $builder->builder();
		$directBuilder->registerExtension($ext = new Symfony);
		$directBuilder->loadFromExtension($ext->getAlias());

		$builder->register(RouteProvider::CACHE_KEY, Memory::class);

		$builder->setParameter(MiddlewarePass::TAG_NAME, ['']);
		$builder->addCompilerPass(new MiddlewarePass(), PassConfig::TYPE_OPTIMIZE);

		$builder->factory(ServerRequestInterface::class, Factory::class, 'createFromGlobals');

		$builder->register($this->applicationTargetClass, $this->applicationActualClass)
				->setArguments(
					[
						'$slim'       => $builder->reference(App::class),
						'$middleware' => '%' . MiddlewarePass::TAG_NAME . '%',
						'$request'    => $builder->reference(ServerRequestInterface::class)
					]
				)
				->setPublic(true);
	}
}
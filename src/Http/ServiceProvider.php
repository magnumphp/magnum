<?php

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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ServiceProvider
 *
 * This class needs to remember the state of certain calls to ensure they always return the same objects, these are
 * stored in the instances property
 *
 * @package Magnum\Http
 */
class ServiceProvider
	implements Provider
{
	public function register(Builder $builder)
	{
		$directBuilder = $builder->builder();
		$directBuilder->registerExtension($ext = new Symfony);
		$directBuilder->loadFromExtension($ext->getAlias());
		$builder->register(RouteProvider::CACHE_KEY, Memory::class);

		$builder->setParameter(MiddlewarePass::TAG_NAME, ['']);
		$builder->addCompilerPass(new MiddlewarePass(), PassConfig::TYPE_OPTIMIZE);

		$builder->factory(ServerRequestInterface::class, Factory::class, 'createFromGlobals');

		$builder->register(Application::class)
				->setSynthetic(true)
				->setArguments(
					[
						'$app'     => new Reference(App::class),
						'$request' => new Reference(ServerRequestInterface::class)
					]
				)
				->setPublic(true);
	}
}
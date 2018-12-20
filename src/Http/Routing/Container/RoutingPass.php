<?php

namespace Magnum\Http\Routing\Container;

use FastRoute\Dispatcher;
use Magnum\Http\Routing\Cache;
use Magnum\Http\Routing\RouteCollector;
use Magnum\Http\Routing\RouteProvider;
use Magnum\Http\Routing\Router;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Grabs all services that are tagged in the container as "routing.provider" and calls them to register their routes
 * with the RouteCollector.
 *
 * @package Magnum\Http\Routing
 */
class RoutingPass
	implements CompilerPassInterface
{
	const TAG_NAME                = 'routing.provider';
	const PARAM_CACHE_ENABLED     = "routing.use_cache";
	const PARAM_CACHE_PATH        = "routing.cache_path";

	/**
	 * {@inheritdoc}
	 */
	public function process(ContainerBuilder $container)
	{
		$cacheEnabled = $container->hasParameter(self::PARAM_CACHE_ENABLED)
			? $container->getParameter(self::PARAM_CACHE_ENABLED)
			: false;

		$cache = $container->get(Cache::class);
		if (!$cacheEnabled || !$cache->has(Cache::DISPATCH_DATA_KEY)) {
			list($namedRoutes, $dispatchData) = $this->resolveRoutes($container);
			if ($cacheEnabled) {
				$cache->set(Cache::NAMED_ROUTES_KEY, $namedRoutes);
				$cache->set(Cache::DISPATCH_DATA_KEY, $dispatchData);
			}
		}
		else {
			$namedRoutes  = $cache->get(Cache::NAMED_ROUTES_KEY);
			$dispatchData = $cache->get(Cache::DISPATCH_DATA_KEY);
		}

		$container->getDefinition(Router::class)
				  ->setArgument('$namedRoutes', $namedRoutes);
		$container->getDefinition(Dispatcher::class)
				  ->setArgument('$data', $dispatchData);
	}

	/**
	 * Resolves the routes from the providers
	 *
	 * @param ContainerBuilder $container
	 * @return array
	 * @throws \Exception
	 */
	protected function resolveRoutes(ContainerBuilder $container)
	{
		/** @var RouteCollector $routeCollector */
		$routeCollector = $container->get(RouteCollector::class);
		foreach ($container->findTaggedServiceIds(self::TAG_NAME, true) as $id => $tags) {
			$provider = $container->has($id) ? $container->get($id) : new $id;
			if ($provider instanceof RouteProvider) {
				$provider->register($routeCollector);
			}
		}

		return [
			$routeCollector->namedRoutes(),
			$routeCollector->dispatchData()
		];
	}
}
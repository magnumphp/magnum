<?php

/**
 * @file
 * Contains Magnum\Http\Routing\Container\RoutingPass
 */

namespace Magnum\Http\Routing\Container;

use FastRoute\Dispatcher;
use Magnum\Container\Builder;
use Magnum\Http\Routing\RouteCollector;
use Magnum\Http\Routing\RouteProvider;
use Magnum\Http\Routing\Router;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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
	const TAG_NAME = 'routing.provider';

	/**
	 * @var string The phase of the RoutingPass that we are working on
	 */
	protected $phase;

	public function __construct($phase)
	{
		if ($phase !== PassConfig::TYPE_BEFORE_OPTIMIZATION && $phase !== PassConfig::TYPE_OPTIMIZE) {
			throw new \InvalidArgumentException(
				sprintf(
					"Only %s or %s are accepted phases",
					PassConfig::TYPE_BEFORE_OPTIMIZATION,
					PassConfig::TYPE_OPTIMIZE
				)
			);
		}

		$this->phase = $phase;
	}

	/**
	 * Registers the compiler passes with the builder.
	 *
	 * Used because we have two passes:
	 *  1) handles the setup of the Router/Dispatcher params
	 *  2) handles the route parameters
	 *
	 * @param Builder $builder
	 */
	public static function registerWithBuilder(Builder $builder)
	{
		$builder->addCompilerPass(new RoutingPass(PassConfig::TYPE_OPTIMIZE), PassConfig::TYPE_OPTIMIZE);
		$builder->addCompilerPass(
			new RoutingPass(PassConfig::TYPE_BEFORE_OPTIMIZATION),
			PassConfig::TYPE_BEFORE_OPTIMIZATION
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(ContainerBuilder $container)
	{
		$this->{$this->phase}($container);
	}

	/**
	 * Called during the before optimization phase of the Compiler Passes
	 *
	 * @param $container
	 */
	protected function beforeOptimization($container)
	{
		$container->getDefinition(Router::class)
				  ->setArgument('$namedRoutes', '%routing.named_routes%');
		$container->getDefinition(Dispatcher::class)
				  ->setArgument('$data', '%routing.dispatch_data%');
		$container->setParameter('routing.named_routes', ['']);
		$container->setParameter('routing.dispatch_data', ['']);
	}

	/**
	 * Called during the Optimization phase of the Compiler Passes
	 *
	 * @param $container
	 */
	protected function optimization($container)
	{
		/** @var RouteCollector $routeCollector */
		$routeCollector = $container->get(RouteCollector::class);
		foreach ($container->findTaggedServiceIds(self::TAG_NAME, true) as $id => $tags) {
			$provider = $container->has($id) ? $container->get($id) : new $id;
			if ($provider instanceof RouteProvider) {
				$provider->routes($routeCollector);
			}
		}

		$container->setParameter('routing.named_routes', $routeCollector->namedRoutes());
		$container->setParameter('routing.dispatch_data', $routeCollector->dispatchData());
	}
}
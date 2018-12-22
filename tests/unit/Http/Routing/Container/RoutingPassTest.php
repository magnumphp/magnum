<?php

namespace Magnum\Http\Routing\Container;

use FastRoute\Dispatcher;
use Magnum\Http\Routing\Cache;
use Magnum\Http\Routing\Cache\Memory;
use Magnum\Http\Routing\RouteCollector;
use Magnum\Http\Routing\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RoutingPassTest
	extends TestCase
{
	/**
	 * @var ContainerBuilder
	 */
	protected $container;

	protected $expected = [
		'GET' => [
			'/' => [
				'home',
				[
					'home'
				]
			]
		]
	];

	public function setUp()
	{
		$cb = new ContainerBuilder();
		$cb->addCompilerPass(new RoutingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);

		// custom class for the routeCollector to register we have routes...
		$cb->register(Cache::class, Memory::class);
		$cb->register(RouteCollector::class, StubCollector::class)->setPublic(true);
		$cb->register(Dispatcher::class, StubDispatcher::class)->setPublic(true);
		$cb->register(Router::class, Router\Basic::class)
		   ->setArgument('$dispatcher', new Reference(Dispatcher::class))
		   ->setPublic(true);

		$cb->register(StubProvider::class)->addTag(RoutingPass::TAG_NAME);

		$this->container = $cb;
	}

	public function testCapturesProviders()
	{
		$this->container->compile();

		self::assertAttributeEquals($this->expected, 'data', $this->container->get(Dispatcher::class));
		self::assertAttributeEquals($this->expected, 'namedRoutes', $this->container->get(Router::class));
	}

	public function testCacheIsUsedWhenEnabledAndSet()
	{
		$this->container->setParameter(RoutingPass::PARAM_CACHE_ENABLED, true);
		$cache = $this->container->get(Cache::class);
		$cache->set(Cache::NAMED_ROUTES_KEY, ['kakaw']);
		$cache->set(Cache::DISPATCH_DATA_KEY, ['test']);

		$this->container->compile();

		self::assertAttributeEquals(['test'], 'data', $this->container->get(Dispatcher::class));
		self::assertAttributeEquals(['kakaw'], 'namedRoutes', $this->container->get(Router::class));
	}

	public function testCacheIsSavedWhenEnabledAndNotSet()
	{
		$this->container->setParameter(RoutingPass::PARAM_CACHE_ENABLED, true);
		$cache = $this->container->get(Cache::class);

		$this->container->compile();

		self::assertEquals($this->expected, $cache->get(Cache::DISPATCH_DATA_KEY));
		self::assertEquals($this->expected, $cache->get(Cache::NAMED_ROUTES_KEY));
	}
}
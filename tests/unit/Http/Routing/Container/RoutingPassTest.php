<?php

namespace Magnum\Http\Routing\Container;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Magnum\Container\Builder;
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
		$cb->addCompilerPass(
			new RoutingPass(PassConfig::TYPE_BEFORE_OPTIMIZATION),
			PassConfig::TYPE_BEFORE_OPTIMIZATION,
			10
		);
		$cb->addCompilerPass(new RoutingPass(PassConfig::TYPE_OPTIMIZE), PassConfig::TYPE_OPTIMIZE, 10);

		// custom class for the routeCollector to register we have routes...
		$cb->register(Cache::class, Memory::class);
		$cb->register(RouteCollector::class, StubCollector::class)->setPublic(true);
		$cb->register(Dispatcher::class, StubDispatcher::class)->setPublic(true);
		$cb->register(RouteParser::class, RouteParser\Std::class)->setPublic(true);
		$cb->register(Router::class, Router\Basic::class)
		   ->setArgument('$dispatcher', new Reference(Dispatcher::class))
		   ->setArgument('$routeParser', new Reference(RouteParser::class))
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

	public function testConstructorThrowsExceptionOnBadPhase()
	{
		$this->expectException(\InvalidArgumentException::class);
		new RoutingPass('bogus');
	}

	public function testRegisterWithBuilderRegistersBothPasses()
	{
		$b = new Builder();
		RoutingPass::registerWithBuilder($b);

		$c = $b->builder()->getCompilerPassConfig();

		$has = false;
		foreach ($c->getBeforeOptimizationPasses() as $p) {
			if ($p instanceof RoutingPass) {
				$has = true;
			}
		}
		self::assertTrue($has, 'RoutingPass did not register BeforeOptimization pass');

		$has = false;
		foreach ($c->getOptimizationPasses() as $p) {
			if ($p instanceof RoutingPass) {
				$has = true;
			}
		}
		self::assertTrue($has, 'RoutingPass did not register Optimization pass');
	}
}
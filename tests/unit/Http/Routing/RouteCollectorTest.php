<?php

namespace Magnum\Http\Routing;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;

class RouteCollectorTest
	extends TestCase
{
	protected function generateCollector($method, $path, $name, $middleware)
	{
		$frc = new \FastRoute\RouteCollector(new Std(), new GroupCountBased());
		$rc  = new RouteCollector($frc);

		$rc->{$method}($path, $name, ...$middleware);

		return [$frc, $rc];
	}

	public function provideTestMethods()
	{
		return [
			['GET'],
			['POST'],
			['PUT'],
			['PATCH'],
			['DELETE'],
			['OPTIONS'],
		];
	}

	/**
	 * @dataProvider provideTestMethods
	 */
	public function testMethods($method)
	{
		list($frc, $rc) = $this->generateCollector($method, '/test/path', 'test-name', ['test::class']);

		$routes = $frc->getData();

		self::assertTrue(isset($routes[0][$method]['/test/path']));
		self::assertEquals(['test-name', ['test::class']], $routes[0][$method]['/test/path']);
	}

	public function testAny()
	{
		list($frc, $rc) = $this->generateCollector('any', '/test/path', 'test-name', ['test::class']);

		$routes = $frc->getData();

		self::assertTrue(isset($routes[0]['*']['/test/path']));
		self::assertEquals(['test-name', ['test::class']], $routes[0]['*']['/test/path']);
	}

	public function testDispatchData()
	{
		list($frc, $rc) = $this->generateCollector('any', '/test/path', 'test-name', ['test::class']);

		self::assertEquals($frc->getData(), $rc->dispatchData());
	}

	public function testNamedRoutes()
	{
		list($frc, $rc) = $this->generateCollector('any', '/test/path', 'test-name', ['test::class']);

		$routes = $rc->namedRoutes();
		self::assertArrayHasKey('test-name', $routes);
	}
}
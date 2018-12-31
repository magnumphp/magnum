<?php

namespace Magnum\Http\Routing;

use PHPUnit\Framework\TestCase;

class RouteTest
	extends TestCase
{
	public function testMethods()
	{
		$route = new Route('GET', '/test/path', 'test-name', [self::class]);

		self::assertEquals('GET', $route->method());
		self::assertEquals('/test/path', $route->path());
		self::assertEquals('test-name', $route->name());
		self::assertEquals([self::class], $route->middleware());
	}
}
<?php

namespace Magnum\Http\Routing;

use PHPUnit\Framework\TestCase;

/**
 * Class GoodResultTest
 *
 * @group RoutingResult
 * @package Magnum\Http\Routing
 */
class GoodResultTest
	extends TestCase
{
	/**
	 * @var Result
	 */
	protected $routeResult;

	protected $params = ['test' => 'test'];

	protected $routeName = 'test-good';
	protected $routePath = '/test/good';

	protected function setUp()
	{
		$this->routeResult = Result::fromRoute(
			new Route('GET', $this->routePath, $this->routeName, [self::class]),
			$this->params
		);
	}

	public function testIsResult()
	{
		self::assertInstanceOf(Result::class, $this->routeResult);
	}

	public function testIsSuccessReturnsTrue()
	{
		self::assertTrue($this->routeResult->isSuccess());
	}

	public function testIsFailureReturnsFalse()
	{
		self::assertFalse($this->routeResult->isFailure());
	}

	public function testIsMethodFailureReturnsFalse()
	{
		self::assertFalse($this->routeResult->isMethodFailure());
	}

	public function testRouteReturnsRouteObject()
	{
		self::assertInstanceOf(Route::class, $this->routeResult->route());
	}

	public function testPathReturnsPath()
	{
		self::assertEquals($this->routePath, $this->routeResult->path());
	}

	public function testNameReturnsNameFromRoute()
	{
		self::assertEquals($this->routeName, $this->routeResult->name());
	}

	public function testParamsReturnsArray()
	{
		self::assertEquals($this->params, $this->routeResult->params());
	}

	public function testAllowedMethodsIsEmpty()
	{
		self::assertEquals([], $this->routeResult->allowedMethods());
	}
}
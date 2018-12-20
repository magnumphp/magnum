<?php

namespace Magnum\Http\Routing;

use PHPUnit\Framework\TestCase;

/**
 * Class NotFoundResultTest
 *
 * @group RoutingResult
 * @package Magnum\Http\Routing
 */
class NotFoundResultTest
	extends TestCase
{
	/**
	 * @var Result
	 */
	protected $routeResult;

	protected $routePath = '/test/not-found';

	protected function setUp()
	{
		$this->routeResult = Result::fromRouteFailure(
			$this->routePath,
			null
		);
	}

	public function testIsResult()
	{
		self::assertInstanceOf(Result::class, $this->routeResult);
	}

	public function testIsSuccessReturnsFalse()
	{
		self::assertFalse($this->routeResult->isSuccess());
	}

	public function testIsFailureReturnsTrue()
	{
		self::assertTrue($this->routeResult->isFailure());
	}

	public function testIsMethodFailureReturnsFalse()
	{
		self::assertFalse($this->routeResult->isMethodFailure());
	}

	public function testRouteReturnsRouteFalse()
	{
		self::assertFalse($this->routeResult->route());
	}

	public function testPathReturnsPath()
	{
		self::assertEquals($this->routePath, $this->routeResult->path());
	}

	public function testNameReturnsNameFromRoute()
	{
		self::assertFalse($this->routeResult->name());
	}

	public function testParamsReturnsEmptyArray()
	{
		self::assertEquals([], $this->routeResult->params());
	}

	public function testAllowedMethodsIsEmpty()
	{
		self::assertNull($this->routeResult->allowedMethods());
	}
}
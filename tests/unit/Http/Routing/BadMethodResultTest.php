<?php

namespace Magnum\Http\Routing;

use PHPUnit\Framework\TestCase;

/**
 * Class BadMethodResultTest
 *
 * @group RoutingResult
 * @package Magnum\Http\Routing
 */
class BadMethodResultTest
	extends TestCase
{
	/**
	 * @var Result
	 */
	protected $routeResult;

	protected $routePath      = '/test/bad-method';
	protected $allowedMethods = ['GET', 'POST'];

	protected function setUp()
	{
		$this->routeResult = Result::fromRouteFailure(
			$this->routePath,
			$this->allowedMethods
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

	public function testIsMethodFailureReturnsTrue()
	{
		self::assertTrue($this->routeResult->isMethodFailure());
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
		self::assertEquals($this->allowedMethods, $this->routeResult->allowedMethods());
	}
}
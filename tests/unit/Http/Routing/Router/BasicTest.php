<?php

namespace Magnum\Http\Routing\Router;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use Magnum\Http\Routing\Result;
use Magnum\Http\Routing\RouteCollector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class BasicTest
	extends TestCase
{
	/** @var Basic */
	protected $router;
	public function setUp()
	{
		$rc = new RouteCollector(new \FastRoute\RouteCollector(new Std(), new GroupCountBased()));
		$rc->get('/test/get', 'test-get', self::class);
		$this->router = new Basic(
			new \FastRoute\Dispatcher\GroupCountBased($rc->dispatchData()),
			$rc->namedRoutes()
		);
	}

	public function testGoodMatch()
	{
		$uri = $this->createMock(UriInterface::class);
		$uri->method('getPath')->willReturn('/test/get');

		$request = $this->createMock(RequestInterface::class);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getUri')->willReturn($uri);

		$result = $this->router->match($request);
		self::assertInstanceOf(Result::class, $result);
		self::assertTrue($result->isSuccess());
	}

	public function testNotFoundMatch()
	{
		$uri = $this->createMock(UriInterface::class);
		$uri->method('getPath')->willReturn('/test/post');

		$request = $this->createMock(RequestInterface::class);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getUri')->willReturn($uri);

		$result = $this->router->match($request);
		self::assertInstanceOf(Result::class, $result);
		self::assertTrue($result->isFailure());
	}

	public function testBadMethodMatch()
	{
		$uri = $this->createMock(UriInterface::class);
		$uri->method('getPath')->willReturn('/test/get');

		$request = $this->createMock(RequestInterface::class);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getUri')->willReturn($uri);

		$result = $this->router->match($request);
		self::assertInstanceOf(Result::class, $result);
		self::assertTrue($result->isMethodFailure());
		self::assertEquals(['GET'], $result->allowedMethods());
	}
}
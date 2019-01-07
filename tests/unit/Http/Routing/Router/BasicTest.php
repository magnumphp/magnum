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
		$this->router = $this->buildRouter(
			[
				['get', '/test/get', 'test-get']
			]
		);
	}

	protected function buildRouter($routes)
	{
		$rc = new RouteCollector(new \FastRoute\RouteCollector(new Std(), new GroupCountBased()));
		foreach ($routes as $route) {
			$method = array_shift($route);
			array_push($route, self::class);
			$rc->$method(...$route);
		}

		return new Basic(
			new \FastRoute\Dispatcher\GroupCountBased($rc->dispatchData()),
			new Std(),
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

	public function getUriTestsProvider()
	{
		return new UriTestProvider();
	}

	/**
	 * @dataProvider getUriTestsProvider
	 */
	public function testGenerateUri($routes, $expected, $payload)
	{
		self::assertEquals($expected, $this->buildRouter($routes)->generateUri(...$payload));
	}

	public function testGenerateUriAcceptsQueryParams()
	{
		self::assertEquals('/test/get?test=woot', $this->router->generateUri('test-get', null, ['test' => 'woot']));
	}

	public function getEncodedUriTestsProvider()
	{
		return new UriTestProvider(true);
	}

	/**
	 * @dataProvider getEncodedUriTestsProvider
	 */
	public function testMatchWithUnencodedPath($route, $payload, $expectedId)
	{
		$routes = [
			['get', $route, 'test']
		];

		$router = $this->buildRouter($routes);

		$uri = $this->createMock(UriInterface::class);
		$uri->method('getPath')->willReturn($payload);

		$request = $this->createMock(RequestInterface::class);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getUri')->willReturn($uri);

		$result = $router->match($request);
		self::assertTrue($result->isSuccess());
		self::assertEquals($expectedId, $result->params()['id']);
	}

	public function testGenerateUriThrowsExceptionOnMissingRoute()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Route not found: test-post');
		$this->router->generateUri('test-post');
	}

	public function testGenerateUriThrowsExceptionOnMissingParameters()
	{
		$router = $this->buildRouter(
			[
				['post', '/test/{id}', 'test-post']
			]
		);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Route `test-post` is missing parameters.');
		$router->generateUri('test-post');
	}
}
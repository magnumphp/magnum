<?php

namespace Magnum\Http;

use Magnum\Http\Message\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

class TestCase
	extends \PHPUnit\Framework\TestCase
{
	public static function assertStatusCode($code, $response)
	{
		self::assertEquals($code, $response->getStatusCode());
	}

	/**
	 * Returns a mock RequestHandler that should be called once
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildOnceRequestHandler(): RequestHandlerInterface
	{
		$response = $this->createMock(ResponseInterface::class);
		$response->method('getStatusCode')->willReturn(333);

		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->once())->method('handle')->willReturn($response);

		return $handler;
	}

	/**
	 * Returns a mock RequestHandler that should never be called
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildNeverRequestHandler(): RequestHandlerInterface
	{
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->never())->method('handle');

		return $handler;
	}

	/**
	 * Builds a request uri mock object
	 *
	 * This is more static than a real request, but works out to let the request be updated through any handler mocks
	 * and being able to test that the attributes are set.
	 *
	 * @param $method
	 * @param $path
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildRequest($method, $path): ServerRequestInterface
	{
//		$request = (new ServerRequestFactory())->createServerRequest($method, "https://example.com/{$path}", ['host']);

		$request = $this->createMock(ServerRequestInterface::class);
		$uri     = $this->createMock(UriInterface::class);

		$uri->method('getPath')->willReturn($path);
		$request->method('getUri')->willReturn($uri);
		$request->method('getMethod')->willReturn($method);

		$attrs = [];
		$request->method('getAttributes')->willReturnCallback(
			function () use (&$attrs) {
				return $attrs;
			}
		);
		$request->method('getAttribute')->willReturnCallback(
			function ($name) use (&$attrs) {
				return $attrs[$name] ?? null;
			}
		);

		$request->method('withAttribute')->willReturnCallback(
			function ($name, $value) use (&$request, &$attrs) {
				$attrs[$name] = $value;

				return $request;
			}
		);

		return $request;
	}
}

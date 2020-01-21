<?php

namespace Magnum\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
	 * @param $method
	 * @param $path
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildRequest($method, $path): ServerRequestInterface
	{
		$request = $this->createMock(ServerRequestInterface::class);
		$uri     = $this->createMock(UriInterface::class);

		$uri->method('getPath')->willReturn($path);
		$request->attrs = [];
		$request->method('getUri')->willReturn($uri);
		$request->method('getMethod')->willReturn($method);

		$request->method('getAttribute')->willReturnCallback(
			function ($name) use (&$request) {
				return $request->attrs[$name] ?? null;
			}
		);

		$request->method('withAttribute')->willReturnCallback(
			function ($name, $value) use (&$request) {
				$request->attrs[$name] = $value;

				return $request;
			}
		);

		return $request;
	}
}

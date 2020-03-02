<?php

namespace Magnum\Http\Middleware;

use Magnum\Http\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionHandlerTest
	extends TestCase
{
	public function testPassesHandlerResponse()
	{
		$response = (new ExceptionHandler())
			->process($this->buildRequest('GET', '/'), $this->buildOnceRequestHandler());

		self::assertEquals(333, $response->getStatusCode());
	}

	public function testPassesErrorResponseOnException()
	{
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->method('handle')->willThrowException(new \Exception);

		$response = (new ExceptionHandler())->process($this->buildRequest('GET', '/'), $handler);

		self::assertEquals(500, $response->getStatusCode());
	}

	public function testPassesErrorResponseOnThrowable()
	{
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->method('handle')->willThrowException($this->createMock(\Throwable::class));

		$response = (new ExceptionHandler())->process($this->buildRequest('GET', '/'), $handler);

		self::assertEquals(500, $response->getStatusCode());
	}

	public function testErrorMessageIsSentInHeader()
	{
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->method('handle')->willThrowException(new \Exception);

		$response = (new ExceptionHandler(true))->process($this->buildRequest('GET', '/'), $handler);

		self::assertTrue($response->hasHeader('x-magnum-error'));
	}
}
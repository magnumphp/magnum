<?php

namespace Magnum\Http\Middleware;

use GuzzleHttp\Psr7\BufferStream;
use Magnum\Http\TestCase;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\ResponseEmitter;

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */
class ResponderTest
	extends TestCase
{
	protected function process($response)
	{
		$request = $this->createMock(ServerRequestInterface::class);
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->once())
				->method('handle')
				->willReturn($response);

		return (new Responder())->process($request, $handler);
	}

	public function testProcessDefaults()
	{
		$response = Factory::createResponse();
		$response->getBody()->write('Hello');

		$this->process($response);

		$this->expectOutputString('Hello');
	}

	public function testProcessUsesCustomEmitter()
	{
		$request  = $this->createMock(ServerRequestInterface::class);
		$handler  = $this->createMock(RequestHandlerInterface::class);
		$response = Factory::createResponse();

		$response->getBody()->write('Hello');
		$handler->expects($this->once())
				->method('handle')
				->willReturn($response);

		$emitter = $this->createMock(ResponseEmitter::class);

		// this is the assert
		$emitter->expects($this->once())->method('emit');

		(new Responder($emitter))->process($request, $handler);
	}

	public function testProcessEnsuresEmptyBodyOnHead()
	{
		$request  = $this->createMock(ServerRequestInterface::class);
		$handler  = $this->createMock(RequestHandlerInterface::class);
		$response = Factory::createResponse();

		$response->getBody()->write('Hello');
		$handler->expects($this->once())
				->method('handle')
				->willReturn($response);
		$request->method('getMethod')
				->willReturn('HEAD');

		(new Responder)->process($request, $handler);

		$this->expectOutputString('');
	}

	public function testEmptyStreamHonorsCustomStreamFactory()
	{
		$request  = $this->createMock(ServerRequestInterface::class);
		$handler  = $this->createMock(RequestHandlerInterface::class);
		$response = Factory::createResponse();

		$response->getBody()->write('Hello');
		$handler->expects($this->once())
				->method('handle')
				->willReturn($response);
		$request->method('getMethod')
				->willReturn('HEAD');

		$responder = new Responder();
		$factory = $this->createMock(StreamFactoryInterface::class);
		$factory->expects($this->once())->method('createStream')->willReturn($buffer = new BufferStream);
		$responder->setStreamFactory($factory);

		$response = $responder->process($request, $handler);

		self::assertEquals($buffer, $response->getBody());
		self::assertEquals('', $buffer->getContents());
	}
}
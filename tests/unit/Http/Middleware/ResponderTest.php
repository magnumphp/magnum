<?php

namespace Magnum\Http\Middleware;

use Magnum\HeaderStack;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */
class ResponderTest
	extends TestCase
{
	public function setUp()
	{
		HeaderStack::reset();
	}

	public function tearDown()
	{
		HeaderStack::reset();
	}

	public function xtestReturnsErrorResponse()
	{
		$mw = new Responder();
		self::assertStatusCode(
			500,
			$mw->process($this->createMock(ServerRequestInterface::class))
		);
	}

	protected function process($response)
	{
		$request = $this->createMock(ServerRequestInterface::class);
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->once())
				->method('handle')
				->willReturn($response);

		return (new Responder())->process($request, $handler);
	}

	public function testRespond()
	{
		$response = Factory::createResponse();
		$response->getBody()->write('Hello');

		$this->process($response);

		$this->expectOutputString('Hello');
	}

	public function testRespondNoContent()
	{
		$response = Factory::createResponse();
		$this->process($response);
		$this->assertEquals(false, HeaderStack::has('Content-Type'));
		$this->assertEquals(false, HeaderStack::has('Content-Length'));
		$this->expectOutputString('');
	}

	public function testResponseReplacesPreviouslySetHeaders()
	{
		$response = Factory::createResponse(200, 'OK')
			->withHeader('X-Foo', 'baz1')
			->withAddedHeader('X-Foo', 'baz2');
		$this->process($response);
		$expectedStack = [
			['header' => 'X-Foo: baz1', 'replace' => true, 'status_code' => null],
			['header' => 'X-Foo: baz2', 'replace' => false, 'status_code' => null],
			['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
		];
		$this->assertSame($expectedStack, HeaderStack::stack());
	}

	public function testIsResponseEmptyWithNonEmptyBodyAndTriggeringStatusCode()
	{
		$response = Factory::createResponse(204);
		$response->getBody()->write('Hello');
		$this->process($response);
		$this->expectOutputString('');
	}


	public function testHeadReturnsEmptyResponse()
	{
		$request = $this->createMock(ServerRequestInterface::class);
		$handler = $this->createMock(RequestHandlerInterface::class);

		$response = Factory::createResponse(200);

		$request->method('getMethod')->willReturn('HEAD');
		$handler->expects($this->once())
				->method('handle')
				->willReturn($response);

		(new Responder())->process($request, $handler);

		$expectedStack = [
			['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
		];

		self::assertSame($expectedStack, HeaderStack::stack());
	}
}
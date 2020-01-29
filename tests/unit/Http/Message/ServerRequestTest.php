<?php

namespace Magnum\Http\Message;

use Magnum\Http\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestTest
	extends TestCase
{
	public function testGetParsedBodyCaches()
	{
		$sri = $this->createMock(ServerRequestInterface::class);
		$sri->expects(self::once())
			->method('getParsedBody')
			->willReturn(['kakaw']);

		$sr = new ServerRequest($sri);
		self::assertSame($sr->getParsedBody(), $sr->getParsedBody());
	}

	public function testGetQueryParams()
	{
		$sri = $this->createMock(ServerRequestInterface::class);
		$sri->expects(self::once())
			->method('getQueryParams')
			->willReturn(['kakaw']);

		$sr = new ServerRequest($sri);
		self::assertSame($sr->getQueryParams(), $sr->getQueryParams());
	}

	public function testGetMediaType()
	{
		$sri = $this->createMock(ServerRequestInterface::class);
		$sri->expects(self::once())
			->method('getHeader')
			->with('Content-Type')
			->willReturn(['text/html; charset=UTF-8']);

		$sr = new ServerRequest($sri);
		$r  = $sr->getMediaType();
		self::assertEquals('text/html', $r);
		self::assertSame($sr->getMediaType(), $sr->getMediaType());
	}

	public function testGetMediaTypeParams()
	{
		$sri = $this->createMock(ServerRequestInterface::class);
		$sri->expects(self::once())
			->method('getHeader')
			->with('Content-Type')
			->willReturn(['text/html; charset=UTF-8']);

		$sr = new ServerRequest($sri);
		self::assertEquals(['charset' => 'UTF-8'], $sr->getMediaTypeParams());
		self::assertSame($sr->getMediaTypeParams(), $sr->getMediaTypeParams());
	}

	public function testGetParams()
	{
		$sri = $this->createMock(ServerRequestInterface::class);
		$sri->expects(self::once())
			->method('getQueryParams')
			->willReturn(['t' => 'kakaw']);
		$sri->expects(self::once())
			->method('getParsedBody')
			->willReturn(['r' => 'boom']);

		$sr = new ServerRequest($sri);
		self::assertEquals(['t' => 'kakaw', 'r' => 'boom'], $sr->getParams());
		self::assertSame($sr->getParams(), $sr->getParams());
	}

	public function testPropertyAccess()
	{
		$sri = $this->createMock(ServerRequestInterface::class);
		$sri->expects(self::once())
			->method('getParsedBody')
			->willReturn(['kakaw']);

		$sr = new ServerRequest($sri);
		self::assertEquals(['kakaw'], $sr->parsedBody);
		self::assertSame($sr->parsedBody, $sr->parsedBody);
	}

	public function testPropertyAccessThrowsException()
	{
		$this->expectException(\RuntimeException::class);
		(new ServerRequest($this->createMock(ServerRequestInterface::class)))->kakaw;
	}
}
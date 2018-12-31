<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\TestCase;

class MutableContextTest
	extends TestCase
{
	public function testArrayInConstructor()
	{
		$payload = ['test' => 'context'];
		$mc      = new MutableContext($payload);

		self::assertEquals($payload, $mc->provide('test'));
	}

	public function testSet()
	{
		$mc = new MutableContext();
		$mc->set('test', 'context');

		self::assertEquals(['test' => 'context'], $mc->provide('test'));
	}

	public function testAcceptsAnything()
	{
		$mc = new MutableContext();
		self::assertTrue($mc->accepts(''));
		self::assertTrue($mc->accepts(null));
		self::assertTrue($mc->accepts('test'));
	}

	public function testProvidesReturnsArray()
	{
		$payload = ['test' => 'context'];
		$mc = new MutableContext($payload);
		self::assertEquals($payload, $mc->provide(''));
		self::assertEquals($payload, $mc->provide(null));
		self::assertEquals($payload, $mc->provide('test'));
	}
}
<?php

namespace Magnum\Container\Param;

use PHPUnit\Framework\TestCase;

class BooleanParamTest
	extends TestCase
{
	public function testTrue()
	{
		self::assertEquals('true', (string)new BooleanParam(true));
	}

	public function testFalse()
	{
		self::assertEquals('false', (string)new BooleanParam(false));
	}
}
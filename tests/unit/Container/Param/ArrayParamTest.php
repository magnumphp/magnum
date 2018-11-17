<?php

namespace Magnum\Container\Param;

use PHPUnit\Framework\TestCase;

class ArrayParamTest
	extends TestCase
{
	public function testStringification()
	{
		self::assertEquals('array(null, null)', (string)new ArrayParam(null, null));
	}
}
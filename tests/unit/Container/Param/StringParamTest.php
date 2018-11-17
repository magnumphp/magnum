<?php

namespace Magnum\Container\Param;

use PHPUnit\Framework\TestCase;

class StringParamTest
	extends TestCase
{
	public function provideStrings()
	{
		return [
			['test', "'test'"],
			['test"', "'test\"'"],
			['ƒun', "'ƒun'"],
			['test\'test', "'test\\'test'"],
			["test'test", "'test\\'test'"]
		];
	}

	/**
	 * @dataProvider provideStrings
	 */
	public function testStringification($payload, $expected)
	{
		self::assertEquals($expected, (string)new StringParam($payload));
	}
}
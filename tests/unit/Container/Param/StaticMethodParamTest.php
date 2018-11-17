<?php

namespace Magnum\Container\Param;

use Magnum\Container\TestCase;

class StaticMethodParamTest
	extends TestCase
{
	public function provideStrings()
	{
		$class = ArrayParam::class;

		return [
			[
				[$class, 'test'],
				"\\{$class}::test()"
			],
			[
				[$class, 'test', [1, 2]],
				"\\{$class}::test(array(1, 2))"
			],
			[
				[$class, 'test', 'testing'],
				"\\{$class}::test('testing')"
			],
			[
				[$class, 'test', 'ƒun"', 2, new StringParam('test2')],
				"\\{$class}::test('ƒun\"', 2, 'test2')"
			],
		];
	}

	/**
	 * @dataProvider provideStrings
	 */
	public function testStringification($payload, $expected)
	{
		self::assertEquals(
			$expected,
			(string)new StaticMethodParam(...$payload)
		);
	}
}
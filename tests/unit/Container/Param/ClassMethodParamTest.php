<?php

namespace Magnum\Container\Param;


use Magnum\Container\TestCase;

class ClassMethodParamTest
	extends TestCase
{
	public function provideStrings()
	{
		$class = ArrayParam::class;
		$test = '('.$this->toSingletonString($class).')';

		return [
			[
				[$class, 'test'],
				"{$test}->test()"
			],
			[
				[$class, 'test', [1, 2]],
				"{$test}->test(array(1, 2))"
			],
			[
				[$class, 'test', 'testing'],
				"{$test}->test('testing')"
			],
			[
				[$class, 'test', 'ƒun"', 2, new StringParam('test2')],
				"{$test}->test('ƒun\"', 2, 'test2')"
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
			(string)new ClassMethodParam(...$payload)
		);
	}
}
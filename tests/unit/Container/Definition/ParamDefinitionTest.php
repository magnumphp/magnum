<?php

namespace Magnum\Container\Definition;

use Magnum\Container\Param\ArrayParam;
use Magnum\Container\Param\BooleanParam;
use Magnum\Container\Param\StaticMethodParam;
use Magnum\Container\Param\StringParam;
use Magnum\Container\TestCase;

class ParamDefinitionTest
	extends TestCase
{
	public function provideDefinitions()
	{
		$class = ArrayParam::class;

		return [
			[
				new StringParam('test'),
				"return 'test';"
			],
			[
				new BooleanParam(true),
				"return true;"
			],
			[
				new StaticMethodParam(StaticMethodParam::class, 'test'),
				"return \\" . StaticMethodParam::class . "::test();"
			],
		];
	}

	/**
	 * @dataProvider provideDefinitions
	 */
	public function testParam($payload, $expected)
	{
		$d = new ParamDefinition($payload);
		self::assertEquals(
			$expected,
			trim($d->toPhpCode([]))
		);
	}
}
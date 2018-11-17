<?php

namespace Magnum\Container\Definition;

use Magnum\Container\Param\ArrayParam;
use Magnum\Container\Param\StringParam;
use Magnum\Container\TestCase;

class FactoryDefinitionTest
	extends TestCase
{
	public function provideDefinitions()
	{
		$class = ArrayParam::class;

		return [
			[
				[$class, 'test'],
				"return \\{$class}::test();"
			],
			[
				[$class, 'test', [1, 2]],
				"return \\{$class}::test(array(1, 2));"
			],
			[
				[$class, 'test', 'testing'],
				"return \\{$class}::test('testing');"
			],
			[
				[$class, 'test', 'ƒun"', 2, new StringParam('test2')],
				"return \\{$class}::test('ƒun\"', 2, 'test2');"
			],
		];
	}

	/**
	 * @dataProvider provideDefinitions
	 */
	public function testToPhpCode($payload, $expected)
	{
		$d = new FactoryDefinition($payload[0], ... $payload);
		self::assertEquals(
			$expected,
			trim($d->toPhpCode([]))
		);
	}
}
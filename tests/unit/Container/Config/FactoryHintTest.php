<?php

namespace Magnum\Container\Config;

use Magnum\Container\Definition\FactoryDefinition;
use Magnum\Container\Definition\ParamDefinition;
use Magnum\Container\Param\StringParam;
use Magnum\Container\TestCase;

class FactoryHintTest
	extends TestCase
{
	public function testToDefinitions()
	{
		$h = new FactoryHint(self::class, 'setup');
		self::assertEquals(
			[
				'test' => new FactoryDefinition('test', self::class, 'setup')
			],
			$h->toDefinitions([], 'test', 'singleton')
		);
	}
}
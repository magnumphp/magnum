<?php

namespace Magnum\Container\Config;

use Magnum\Container\Definition\ParamDefinition;
use Magnum\Container\Param\StringParam;
use Magnum\Container\TestCase;

class ParamHintTest
	extends TestCase
{
	public function testParam()
	{
		$p = new StringParam('test');
		$d = new ParamHint($p);
		self::assertEquals($p, $d->param());
	}

	public function testToDefinitions()
	{
		$p = new StringParam('test');
		$d = new ParamDefinition($p);
		$h = new ParamHint($p);
		self::assertEquals(
			[
				'test' => $d
			],
			$h->toDefinitions([], 'test', 'singleton')
		);
	}
}
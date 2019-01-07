<?php

namespace Magnum\Container\Stub;

class TestFactory
{
	public static function build(ConstructorA $a)
	{
		return $a;
	}
}
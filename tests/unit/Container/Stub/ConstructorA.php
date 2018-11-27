<?php

namespace Magnum\Container\Stub;

class ConstructorA
{
	public static function test()
	{
		return new self;
	}
	public function __construct($a = null)
	{
		$this->a = $a;
	}
}
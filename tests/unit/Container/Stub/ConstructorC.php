<?php

namespace Magnum\Container\Stub;

class ConstructorC
{
	public function __construct(ConstructorA $a,... $test)
	{
		$this->a = $a;
		$this->test = $test;
	}
}
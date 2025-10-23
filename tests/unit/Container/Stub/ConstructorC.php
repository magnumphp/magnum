<?php

namespace Magnum\Container\Stub;

class ConstructorC
{
	public $a;
	public $test;
	public function __construct(ConstructorA $a, ...$test)
	{
		$this->a    = $a;
		$this->test = $test;
	}
}
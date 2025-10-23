<?php

namespace Magnum\Container\Stub;

class ConstructorB
{
	public $a;

	public function __construct(ConstructorA $a)
	{
		$this->a = $a;
	}
}
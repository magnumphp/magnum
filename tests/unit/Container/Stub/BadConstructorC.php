<?php

namespace Magnum\Container\Stub;

class BadConstructorC
{
	public function __construct(ConstructorX $x)
	{
		$this->x = $x;
	}
}
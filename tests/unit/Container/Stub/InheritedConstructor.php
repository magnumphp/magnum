<?php

namespace Magnum\Container\Stub;

class InheritedConstructor
	extends ConstructorB
{
	public function __construct(StubProvider $sp, $a)
	{
		$this->sp = $sp;
		$this->a = $a;
	}
}
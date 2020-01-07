<?php

namespace Magnum\Container\Stub;

class ConstructorA
{
	public $modified = false;

	public static function test()
	{
		return new self;
	}

	public function __construct($a = null)
	{
		$this->a = $a;
	}

	public function modify()
	{
		$this->modified = true;
	}
}
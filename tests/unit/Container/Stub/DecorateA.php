<?php

namespace Magnum\Container\Stub;

class DecorateA
{
	public $parent;

	public function __construct(ConstructorA $parent)
	{
		$this->parent = $parent;
	}

	public function modify()
	{
		return true + $this->parent->modified;
	}
}
<?php

namespace Magnum\Container;

class TestCase
	extends \PHPUnit\Framework\TestCase
{
	public function toSingletonString($class)
	{
		$hash = str_replace('\\', '__', $class);
		return "\$this->singletonEntries['{$class}'] ?? \$this->{$hash}()";
	}
}
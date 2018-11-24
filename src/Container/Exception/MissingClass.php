<?php

namespace Magnum\Container\Exception;

use WoohooLabs\Zen\Exception\ContainerException;

class MissingClass
	extends ContainerException
{
	protected $class;

	public function __construct($className)
	{
		$this->class = $className;
		parent::__construct("Cannot inject class: {$className}");
	}

	public function class()
	{
		return $this->class;
	}
}
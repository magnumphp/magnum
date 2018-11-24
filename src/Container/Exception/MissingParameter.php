<?php

namespace Magnum\Container\Exception;

use WoohooLabs\Zen\Exception\ContainerException;

class MissingParameter
	extends ContainerException
{
	public function __construct($className, $paramName)
	{
		parent::__construct(
			"Type declaration or '@param' PHPDoc comment for constructor parameter '{$paramName}' in '" .
			"class '{$className}' is missing or it is not a class!"
		);
	}
}
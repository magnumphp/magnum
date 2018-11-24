<?php
declare(strict_types=1);

namespace Magnum\Container\Fixture;

class NonOptionalConstructor
{
	public function __construct($param, $altParam = null)
	{
		$this->param = $param;
		$this->altParam = $altParam;
	}
}

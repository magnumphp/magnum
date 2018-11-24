<?php

namespace Magnum\Container\Config;

use Magnum\Container\Param\Param;

class ContextDependentParamHint
	extends \WoohooLabs\Zen\Config\Hint\ContextDependentDefinitionHint
{
	protected $defaultHint;

	protected $hints = [];

	public function __construct($defaultHint = null)
	{
		$this->defaultHint = $defaultHint instanceof Param ? $defaultHint : ParamHint::create($defaultHint);
	}

	public function resolve($id)
	{
		if (isset($this->hints[$id])) {
			return $this->hints[$id];
		}

		return $this->defaultHint;
	}

	public function when($id)
	{
		$this->stack[] = $id;
	}

	public function wants($id)
	{
		$this->stack[] = $id;
	}

	public function set($hint)
	{

		//		$this->hints[$this->stack[0]] =
	}
}
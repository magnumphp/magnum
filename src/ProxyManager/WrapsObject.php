<?php

namespace Magnum\ProxyManager;

trait WrapsObject
{
	protected $instance;

	public function __call($method, $args)
	{
		return call_user_func_array(array($this->instance, $method), $args);
	}
}

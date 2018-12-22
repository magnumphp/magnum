<?php
namespace Magnum\Http\Routing\Container;

use FastRoute\Dispatcher;

class StubDispatcher
	implements Dispatcher
{
	public $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function dispatch($httpMethod, $uri)
	{
		// TODO: Implement dispatch() method.
	}

}
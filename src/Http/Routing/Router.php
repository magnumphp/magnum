<?php

namespace Magnum\Http\Routing;

use Psr\Http\Message\RequestInterface;

interface Router
{
	public function match(RequestInterface $request): Result;
}
<?php

namespace Magnum\Http\Stub\Action;

use Magnum\Http\Message\DecoratedRequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class InterfaceRequestDecorator
	implements DecoratedRequestHandler
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->request = $request;
	}

	protected function run(\InterfaceKakaw $request)
	{
		// nothing
	}
}

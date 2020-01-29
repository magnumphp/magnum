<?php

namespace Magnum\Http\Message\ServerRequest;

use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class GuzzleFactory
	extends AbstractFactory
{
	/**
	 * {@inheritDoc}
	 */
	protected function buildBodyStream(): StreamInterface
	{
		return new CachingStream(new LazyOpenStream('php://input', 'r+'));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function resolveCookies(ServerRequestInterface $request)
	{
		$cookies = [];
		foreach (explode('; ', $request->getHeaderLine('cookie')) as $cookie) {
			list($k, $v) = explode('=', $cookie, 2);
			$cookies[$k] = $v;
		}

		return $cookies;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function resolveFiles(ServerRequestInterface $request)
	{
		return GuzzleServerRequest::normalizeFiles($_FILES);
	}
}
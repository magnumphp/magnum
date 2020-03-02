<?php

/**
 * @file
 * Contains Magnum\Http\Message\ServerRequest\SlimFactory
 */

namespace Magnum\Http\Message\ServerRequest;

use Middlewares\Utils\Factory as MiddlewareFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Cookies;
use Slim\Psr7\UploadedFile;

class SlimFactory
	extends AbstractFactory
{
	/**
	 * {@inheritDoc}
	 */
	protected function buildBodyStream(): StreamInterface
	{
		$factory = MiddlewareFactory::getStreamFactory();

		// slim extends this to handle the caching internally
		return $factory->createStreamFromFile(
			'php://input',
			'r',
			$factory->createStreamFromFile('php://temp', 'wb+')
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function resolveCookies(ServerRequestInterface $request)
	{
		return Cookies::parseHeader($request->getHeaderLine('cookie'));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function resolveFiles(ServerRequestInterface $request)
	{
		return UploadedFile::createFromGlobals($_FILES ?? []);
	}
}
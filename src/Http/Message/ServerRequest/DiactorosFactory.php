<?php

namespace Magnum\Http\Message\ServerRequest;

use function Laminas\Diactoros\parseCookieHeader;
use function Laminas\Diactoros\normalizeUploadedFiles;
use Laminas\Diactoros\PhpInputStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class DiactorosFactory
	extends AbstractFactory
{
	/**
	 * {@inheritDoc}
	 */
	public function buildBodyStream(): StreamInterface
	{
		return new PhpInputStream();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function resolveCookies(ServerRequestInterface $request)
	{
		return parseCookieHeader($request->getHeaderLine('Cookie'));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function resolveFiles(ServerRequestInterface $request)
	{
		return normalizeUploadedFiles($_FILES);
	}
}
<?php

namespace Magnum\Http\Message\ServerRequest;

use League\Uri\Http;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractFactory
{
	const KEY_INPUT = '__INPUT__';

	/**
	 * @var array
	 */
	protected $globals;

	/**
	 * @var \Middlewares\Utils\Factory
	 */
	protected $factory;

	/**
	 * @var array|mixed
	 */
	protected $server;

	/**
	 * @param ServerRequestInterface $request The request
	 *
	 * @return array|null
	 */
	abstract protected function resolveCookies(ServerRequestInterface $request);

	/**
	 * @param ServerRequestInterface $request The request
	 * @return array|null
	 */
	abstract protected function resolveFiles(ServerRequestInterface $request);

	abstract protected function buildBodyStream(): StreamInterface;

	public function __construct($factory, array $globals)
	{
		$this->factory = $factory;
		$this->globals = empty($globals) ? $GLOBALS : $globals;
		$this->server  = $this->globals['_SERVER'] ?? [];
	}

	public function create(): ServerRequestInterface
	{
		$request = $this->createServerRequest();

		// handle cookies
		$cookies = $this->globals['_COOKIE'] ?? $this->resolveCookies($request);
		if (!empty($cookies)) {
			$request = $request->withCookieParams($cookies);
		}

		// handle files
		$files = $this->globals['_FILES'] ?? $this->resolveFiles($request);
		if (!empty($files)) {
			$request = $request->withUploadedFiles($files);
		}

		return $request;
	}

	public function createServerRequest(): ServerRequestInterface
	{
		$request = $this->factory->createServerRequest(
			$this->server['REQUEST_METHOD'] ?? 'GET',
			Http::createFromServer($this->server),
			$this->server
		);

		$body = $this->globals['_POST'] ?? $_POST;
		if (!empty($body)) {
			$request = $request->withParsedBody($body);
		}

		// PSR-7 doesn't have a bulk modifier for headers... and we can't be 100% certain an implementation does
		foreach ($this->server as $key => $value) {
			if (!isset($value)) {
				continue;
			}

			if (strpos($key, 'HTTP_') === 0) {
				$header = substr($key, 5);
			}
			elseif (strpos($key, 'CONTENT_') === 0) {
				$header = $key;
			}

			if (isset($header)) {
				$request = $request->withHeader(strtolower(strtr($header, '_', '-')), $value);
				unset($header);
			}
		}

		return $request
			->withBody($this->globals[self::KEY_INPUT] ?? $this->buildBodyStream())
			->withProtocolVersion(str_replace('HTTP/', '', $this->server['SERVER_PROTOCOL'] ?? '1.1'))
			->withQueryParams($this->globals['_GET'] ?? []);
	}
}
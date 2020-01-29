<?php

/**
 * @file
 * Contains Magnum\Http\Message\ServerRequest
 */

namespace Magnum\Http\Message;

use Psr\Http\Message\RequestInterface;
use Slim\Http\ServerRequest as SlimServerRequest;

/**
 * Extends Slim ServerRequest to prevent re-computation as needed
 *
 * Also adds a generic getter that allows access to the cached data
 *
 * @package Magnum\Http\Message\Request
 */
class ServerRequest
	extends SlimServerRequest
{
	/**
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * @var array|object The cached body parameters, if any.
	 */
	protected $cachedParsedBody;

	/**
	 * @var array The cached query parameters
	 */
	protected $cachedQueryParams;

	/**
	 * @var string|null The cacned serverRequest media type
	 */
	protected $cachedMediaType;

	/**
	 * @var array The cached media type params
	 */
	protected $cachedMediaTypeParams;

	/**
	 * @var array The cached params
	 */
	protected $cachedParams;

	/**
	 * Convenience function to retrieve the cached data via property access
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$prop = "cached".ucfirst($name);
		if (isset($this->$prop)) {
			return $this->$prop;
		}
		elseif (method_exists($this, $method = 'get'.ucfirst($name))) {
			return $this->$method();
		}

		throw new \RuntimeException("Invalid Property: {$name}");
	}

	/**
	 * Overrides the parent to cache the parsedBody
	 *
	 * {@inheritDoc}
	 */
	public function getParsedBody()
	{
		return $this->cachedParsedBody ?? ($this->cachedParsedBody = parent::getParsedBody());
	}

	/**
	 * Overrides the parent to cache the query params
	 *
	 * {@inheritDoc}
	 */
	public function getQueryParams(): array
	{
		return $this->cachedQueryParams ?? ($this->cachedQueryParams = parent::getQueryParams());
	}

	/**
	 * Overrides the parent to cache the media type
	 *
	 * {@inheritDoc}
	 */
	public function getMediaType(): ?string
	{
		return $this->cachedMediaType ?? ($this->cachedMediaType = parent::getMediaType());
	}

	/**
	 * Overrides the parent to cache the media type params
	 *
	 * {@inheritDoc}
	 */
	public function getMediaTypeParams(): array
	{
		return $this->cachedMediaTypeParams ?? ($this->cachedMediaTypeParams = parent::getMediaTypeParams());
	}

	/**
	 * Overrides the parent to cache the params
	 *
	 * {@inheritDoc}
	 */
	public function getParams(): array
	{
		return $this->cachedParams ?? ($this->cachedParams = parent::getParams());
	}
}
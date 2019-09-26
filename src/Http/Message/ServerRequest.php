<?php

namespace Magnum\Http\Message;

use Slim\Http\ServerRequest as SlimServerRequest;

/**
 * Extends Slim ServerRequest to prevent re-computation as needed
 *
 * Also adds a generic getter
 *
 * @package Magnum\Http\Message\Request
 */
class ServerRequest
	extends SlimServerRequest
{
	protected $cachedParsedBody;
	protected $cachedQueryParams;
	protected $cachedMediaType;
	protected $cachedMediaTypeParams;
	protected $cachedParams;

	public function __get($name)
	{
		$prop = "cached{$name}";
		if (isset($this->$prop)) {
			return $this->$prop;
		}
		elseif (method_exists($this, $method = 'get'.ucfirst($name))) {
			return $this->$method();
		}

		throw new \RuntimeException();
	}

	public function getParsedBody()
	{
		return $this->cachedParsedBody ?? ($this->cachedParsedBody = parent::getParsedBody());
	}

	public function getQueryParams(): array
	{
		return $this->cachedQueryParams ?? ($this->cachedQueryParams = parent::getQueryParams());
	}

	public function getMediaType(): ?string
	{
		return $this->cachedMediaType ?? ($this->cachedMediaType = parent::getMediaType());
	}

	public function getMediaTypeParams(): array
	{
		return $this->cachedMediaTypeParams ?? ($this->cachedMediaTypeParams = parent::getMediaTypeParams());
	}

	public function getParams(): array
	{
		return $this->cachedParams ?? ($this->cachedParams = parent::getParams());
	}
}
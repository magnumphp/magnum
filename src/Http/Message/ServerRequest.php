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
	protected $_parsedBody;
	protected $_queryParams;
	protected $_mediaType;
	protected $_mediaTypeParams;
	protected $_params;

	public function __get($name)
	{
		$prop = "_{$name}";
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
		return $this->_parsedBody ?? ($this->_parsedBody = parent::getParsedBody());
	}

	public function getQueryParams(): array
	{
		return $this->_queryParams ?? ($this->_queryParams = parent::getQueryParams());
	}

	public function getMediaType(): ?string
	{
		return $this->_mediaType ?? ($this->_mediaType = parent::getMediaType());
	}

	public function getMediaTypeParams(): array
	{
		return $this->_mediaTypeParams ?? ($this->_mediaTypeParams = parent::getMediaTypeParams());
	}

	public function getParams(): array
	{
		return $this->_params ?? ($this->_params = parent::getParams());
	}
}
<?php

namespace Magnum\Http\Stub;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class HttpFactory
 *
 * factory class for testing
 *
 * @package Magnum\Http\Stub
 */
class HttpFactory
	implements
	ResponseFactoryInterface,
	ServerRequestFactoryInterface,
	StreamFactoryInterface,
	UriFactoryInterface
{
	/**
	 * Check whether Diactoros is available
	 */
	public static function isInstalled(): bool
	{
		return class_exists('Zend\\Diactoros\\Response')
			&& class_exists('Zend\\Diactoros\\ServerRequest')
			&& class_exists('Zend\\Diactoros\\Stream')
			&& class_exists('Zend\\Diactoros\\Uri');
	}

	public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
	{
	}

	public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
	{
	}

	public function createStream(string $content = ''): StreamInterface
	{
	}

	public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
	{
	}

	public function createStreamFromResource($resource): StreamInterface
	{
	}

	public function createUri(string $uri = ''): UriInterface
	{
	}
}
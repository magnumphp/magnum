<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\Responder
 */

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to handle sending the response
 */

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */
class Responder
	implements MiddlewareInterface
{
	use IsMiddleware;

	const DEFAULT_CHUNK_SIZE = 4096;

	/**
	 * @var int The response chunk size
	 */
	protected $responseChunkSize = self::DEFAULT_CHUNK_SIZE;

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		$response = $handler
			? $handler->handle($request)
			: $this->createResponse(500);

		if (strtoupper($request->getMethod()) === 'HEAD') {
			$response = $response->withBody($this->createResponse(200)->getBody());
		}

		return $this->send($response);
	}

	/**
	 * Send the response the client
	 *
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	protected function respond(ResponseInterface $response)
	{
		// Send response
		if (!headers_sent()) {
			// Headers
			foreach ($response->getHeaders() as $name => $values) {
				$first = true;
				foreach ($values as $value) {
					header(sprintf('%s: %s', $name, $value), $first);
					$first = false;
				}
			}

			// Set the status _after_ the headers, because of PHP's "helpful" behavior with location headers.
			// See https://github.com/slimphp/Slim/issues/1730

			// Status
			$statusCode = $response->getStatusCode();
			header(
				sprintf(
					'HTTP/%s %s %s',
					$response->getProtocolVersion(),
					$statusCode,
					$response->getReasonPhrase()
				),
				true,
				$statusCode
			);
		}

		// Body
		if (!$this->isEmptyResponse($response)) {
			$body = $response->getBody();
			if ($body->isSeekable()) {
				$body->rewind();
			}

			$contentLength = (int)$response->getHeaderLine('Content-Length');
			if (!$contentLength) {
				$contentLength = $body->getSize();
			}

			if ($contentLength) {
				while ($contentLength > 0 && !$body->eof()) {
					$data = $body->read(min($this->responseChunkSize, $contentLength));
					echo $data;

					$contentLength -= strlen($data);

					if (connection_status() != CONNECTION_NORMAL) {
						break;
					}
				}
			}
			else {
				while (!$body->eof()) {
					echo $body->read($this->responseChunkSize);
					if (connection_status() != CONNECTION_NORMAL) {
						break;
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Send the response if it's not empty
	 *
	 * @param ResponseInterface|null $response
	 * @return ResponseInterface
	 */
	protected function send(ResponseInterface $response): ResponseInterface
	{
		if ($this->isEmptyResponse($response)) {
			$response = $response->withoutHeader('Content-Type')
								 ->withoutHeader('Content-Length');
		}

		return $this->respond($response);
	}

	/**
	 * Helper method, which returns true if the provided response must not output a body and false
	 * if the response could have a body.
	 *
	 * @see https://tools.ietf.org/html/rfc7231
	 *
	 * @param ResponseInterface $response
	 * @return bool
	 */
	protected function isEmptyResponse(ResponseInterface $response)
	{
		if (method_exists($response, 'isEmpty')) {
			return $response->isEmpty();
		}
		else {
			$size = $response->getBody()->getSize();
		}

		return $size === 0 || in_array($response->getStatusCode(), [204, 205, 304]);
	}
}
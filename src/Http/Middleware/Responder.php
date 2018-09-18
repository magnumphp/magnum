<?php

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Responder
	implements MiddlewareInterface
{
	const DEFAULT_CHUNK_SIZE = 4096;
	protected $responseChunkSize = self::DEFAULT_CHUNK_SIZE;

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		if ($handler) {
			$response = $handler->handle($request);
		}

		if ($this->isEmptyResponse($response)) {
			return $response->withoutHeader('Content-Type')->withoutHeader('Content-Length');
		}

		return $this->respond($response);
	}

	/**
	 * Send the response the client
	 *
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	public function respond(ResponseInterface $response)
	{
		// Send response
		if (!headers_sent()) {
			// Headers
			foreach ($response->getHeaders() as $name => $values) {
				foreach ($values as $value) {
					header(sprintf('%s: %s', $name, $value), false);
				}
			}

			// Set the status _after_ the headers, because of PHP's "helpful" behavior with location headers.
			// See https://github.com/slimphp/Slim/issues/1730

			// Status
			header(
				sprintf(
					'HTTP/%s %s %s',
					$response->getProtocolVersion(),
					$response->getStatusCode(),
					$response->getReasonPhrase()
				)
			);
		}

		// Body
		if (!$this->isEmptyResponse($response)) {
			$body = $response->getBody();
			if ($body->isSeekable()) {
				$body->rewind();
			}
			$chunkSize     = $this->responseChunkSize;
			$contentLength = $response->getHeaderLine('Content-Length');
			if (!$contentLength) {
				$contentLength = $body->getSize();
			}

			if (isset($contentLength)) {
				$amountToRead = $contentLength;
				while ($amountToRead > 0 && !$body->eof()) {
					$data = $body->read(min($chunkSize, $amountToRead));
					echo $data;

					$amountToRead -= strlen($data);

					if (connection_status() != CONNECTION_NORMAL) {
						break;
					}
				}
			}
			else {
				while (!$body->eof()) {
					echo $body->read($chunkSize);
					if (connection_status() != CONNECTION_NORMAL) {
						break;
					}
				}
			}
		}

		return $response;
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

		return in_array($response->getStatusCode(), [204, 205, 304]);
	}
}
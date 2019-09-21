<?php

namespace Magnum\Http\Message\ServerRequest;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class GuzzleFactory
{
	public static function updateRequest(ServerRequestInterface $request, array $globals): ServerRequestInterface
	{
		if (!empty($globals['_FILES'])) {
			$request = $request
				->withUploadedFiles(GuzzleServerRequest::normalizeFiles($globals['_FILES']));
		}

		return $request
			->withBody($globals['_INPUT'] ?? new LazyOpenStream('php://input', 'r+'));
	}
}
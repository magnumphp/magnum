<?php

namespace Magnum\Http\Message\ServerRequest;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\UploadedFile;

class SlimFactory
{
	public static function updateRequest(ServerRequestInterface $request, array $globals): ServerRequestInterface
	{
		if (!empty($globals['_FILES'])) {
			$request = $request->withUploadedFiles(UploadedFile::createFromGlobals($globals['_FILES'] ?? []));
		}

		return $request
			->withBody($globals['_INPUT'] ?? (new StreamFactory())->createStreamFromFile('php://input', 'r+'));
	}
}
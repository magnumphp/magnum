<?php

namespace Magnum\Http\Message\ServerRequest;

use Psr\Http\Message\ServerRequestInterface;
use function Zend\Diactoros\normalizeUploadedFiles;
use Zend\Diactoros\PhpInputStream;

class DiactorosFactory
{
	public static function updateRequest(ServerRequestInterface $request, array $globals): ServerRequestInterface
	{
		if (!empty($globals['_FILES'])) {
			$request = $request->withUploadedFiles(normalizeUploadedFiles($globals['_FILES'] ?? []));
		}

		return $request
			->withBody($globals['_INPUT'] ?? new PhpInputStream());
	}
}
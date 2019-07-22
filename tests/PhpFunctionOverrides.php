<?php

namespace Magnum\Http\Middleware;

use Magnum\HeaderStack;

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * This file exists to allow overriding the various output-related functions
 * in order to test what happens during the `Server::listen()` cycle.
 *
 * These functions include:
 *
 * - headers_sent(): we want to always return false so that headers will be
 *   emitted, and we can test to see their values.
 * - header(): we want to aggregate calls to this function.
 *
 * The HeaderStack class then aggregates that information for us, and the test
 * harness resets the values pre and post test.
 *
 * @see       http://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

/**
 * Have headers been sent?
 *
 * @return false
 */
function headers_sent()
{
	return false;
}

/**
 * Emit a header, without creating actual output artifacts
 *
 * @param string   $string
 * @param bool     $replace
 * @param int|null $statusCode
 */
function header($string, $replace = true, $statusCode = null)
{
	HeaderStack::push(
		[
			'header'      => $string,
			'replace'     => $replace,
			'status_code' => $statusCode,
		]
	);
}

/**
 * Is a file descriptor writable
 *
 * @param string $file
 *
 * @return bool
 */
function is_readable($file)
{
	if (stripos($file, 'non-readable.cache') !== false) {
		return false;
	}
	return true;
}

/**
 * Is a path writable
 *
 * @param string $path
 *
 * @return bool
 */
function is_writable($path)
{
	if (stripos($path, 'non-writable-directory') !== false) {
		return false;
	}
	return true;
}
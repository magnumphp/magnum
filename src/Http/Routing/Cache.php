<?php

/**
 * @file
 * Contains Magnum\Http\Routing\Cache
 */

namespace Magnum\Http\Routing;

/**
 * Class Cache
 *
 * This is a simple file Cache that uses a subset of PSR-16
 *
 * @package Magnum\Http\Routing
 */
interface Cache
{
	const NAMED_ROUTES_KEY = 'names';
	const DISPATCH_DATA_KEY = 'dispatch';

	/**
	 * Returns the data from key
	 *
	 * @param string $key The key to retrieve
	 * @return array The data in the key
	 */
	public function get(string $key): array;

	/**
	 * Returns whether or not the key exists
	 *
	 * @param string $key
	 * @return bool  True if it exists, false otherwise
	 */
	public function has(string $key): bool;

	/**
	 * Sets the data in the cache for the given key
	 *
	 * @param string $key  The key to set
	 * @param array  $data The data to save
	 * @return void
	 */
	public function set(string $key, $data);

	/**
	 * Deletes the key from the cache
	 *
	 * @param string $key
	 * @return bool
	 */
	public function delete(string $key): bool;
}
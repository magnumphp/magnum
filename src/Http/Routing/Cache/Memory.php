<?php

namespace Magnum\Http\Routing\Cache;

use Magnum\Http\Routing\Cache;

/**
 * Class Cache
 *
 * This is a simple file Cache that uses a subset of PSR-16
 *
 * @package Magnum\Http\Routing
 */
class Memory
	implements Cache
{
	/**
	 * NOTE: this is public as it's typically used in testing
	 *
	 * @var array The cache
	 */
	public $cache = [];

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key): array
	{
		return $this->cache[$key] ?? [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set(string $key, $data)
	{
		$this->cache[$key] = $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $key): bool
	{
		unset($this->cache[$key]);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return array_key_exists($key, $this->cache);
	}
}
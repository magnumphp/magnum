<?php

/**
 * @file
 * Contains Magnum\Http\Routing\Cache\File
 */

namespace Magnum\Http\Routing\Cache;

use Magnum\Http\Routing\Cache;

/**
 * Cache for saving/reading file data
 *
 * This is a simple file Cache that uses a subset of PSR-16.
 *
 * @package Magnum\Http\Routing
 */
class File
	implements Cache
{
	/**
	 * @var string The path to where the files are stored
	 */
	protected $cachePath;

	/**
	 * File constructor.
	 *
	 * @param string $cachePath
	 */
	public function __construct(string $cachePath)
	{
		$this->cachePath = $cachePath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key): array
	{
		if (file_exists($file = $this->formatFileName($key))) {
			return require $file;
		}

		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set(string $key, $data)
	{
		if (!is_array($data)) {
			// enforce the array so get works properly
			$data = [$data];
		}

		return @file_put_contents(
			$this->formatFileName($key),
			"<?php return " . var_export($data, true) . ';'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return file_exists($this->formatFileName($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $key): bool
	{
		if (file_exists($file = $this->formatFileName($key))) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Formats the key in to it's file name
	 *
	 * @param string $key The key to format
	 * @return string The file name of the key
	 */
	protected function formatFileName(string $key)
	{
		return "{$this->cachePath}/_routing_{$key}.php";
	}
}
<?php

/**
 * @file
 * Contains Magnum\Container\Compiler\ResolvePathsParameter
 */

namespace Magnum\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Resolves the paths in to the parameter bag
 *
 * @package Magnum\Container\Compiler
 */
class ResolvePathsParameter
	implements CompilerPassInterface
{
	/**
	 * @var array
	 */
	protected $paths = [];

	/**
	 * @var array
	 */
	protected $tags = [];

	/**
	 * Appends a path(s) to the path parameter, optionally tagging them
	 *
	 * @param string          $name  The name of the path parameter
	 * @param string|string[] $paths The path or paths to add
	 * @param string|null     $tag   Whether or not to tag the paths
	 *
	 * @return self
	 */
	public function append(string $name, $paths, ?string $tag = null): self
	{
		list($paths, $tagNameKey) = $this->resolvePathsAndTagNameKey($paths, $name, $tag);

		array_push($this->paths[$name], ...$paths);
		if ($tag) {
			array_push($this->tags[$tag], ...$paths);
			array_push($this->tags[$tagNameKey], ...$paths);
		}

		return $this;
	}

	/**
	 * Prepends a path(s) to the path parameter, optionally tagging them
	 *
	 * @param string          $name  The name of the path parameter
	 * @param string|string[] $paths The path or paths to add
	 * @param string|null     $tag   Whether or not to tag the paths
	 *
	 * @return self
	 */
	public function prepend($name, $paths, ?string $tag = null): self
	{
		list($paths, $tagNameKey) = $this->resolvePathsAndTagNameKey($paths, $name, $tag);

		array_unshift($this->paths[$name], ...$paths);
		if ($tag) {
			array_unshift($this->tags[$tag], ...$paths);
			array_unshift($this->tags[$tagNameKey], ...$paths);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function process(ContainerBuilder $container)
	{
		$data         = $this->paths;
		$data['keys'] = array_keys($this->paths);
		$data['tags'] = array_keys($this->tags);
		foreach ($this->tags as $key => $paths) {
			$data['tag'][$key] = $paths;
		}

		$container->setParameter('paths', $data);
		$container->setParameter('paths.keys', $data['keys']);
		$container->setParameter('paths.tags', $data['tags']);
		foreach ($data['keys'] as $key) {
			$container->setParameter("paths.{$key}", $this->paths[$key]);
		}

		foreach ($data['tags'] as $key) {
			$container->setParameter("paths.tag.{$key}", $data['tag'][$key]);
		}
	}

	/**
	 * Resolves the path to it's expanded parts when applicable
	 *
	 * @param string $path
	 *
	 * @return mixed
	 */
	public function resolve(string $path)
	{
		$this->resolvePathPermutations($path, $paths);

		return count($paths) === 1 ? array_pop($paths) : $paths;
	}

	/**
	 * Prepends a path(s) to the path parameter, optionally tagging them
	 *
	 * @param string      $name  The name of the path parameter
	 * @param mixed       $value The value(s) to set
	 * @param string|null $tag   Whether or not to tag the paths
	 *
	 * @return self
	 */
	public function set(string $name, $value, ?string $tag = null): self
	{
		list($paths, $tagNameKey) = $this->resolvePathsAndTagNameKey($value, $name, $tag);

		$this->paths[$name] = $value;

		return $this;
	}

	/**
	 * Generates the list of path permutations
	 *
	 * @param string $path
	 * @param array  $paths
	 */
	protected function resolvePathPermutations(string $path, &$paths = [])
	{
		if (preg_match_all('/%paths\.([^%\s]+)%/', $path, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$tmp = $this->paths[$match[1]] ?? null;
				if (is_array($tmp)) {
					foreach ($tmp as $matchPath) {
						$this->resolvePathPermutations(str_replace($match[0], $matchPath, $path), $paths);
					}
				}
				else {
					$paths[] = str_replace($match[0], $tmp, $path);
				}
			}
		}
		else {
			$paths[] = $path;
		}
	}

	/**
	 * Resolves the paths & tagNameKey for use in append/prepend
	 *
	 * @param string|string[] $paths List of paths to remove from the arrays
	 * @param string          $name  Paths parameter name
	 * @param string|null     $tag   Paths tag name
	 *
	 * @return array
	 */
	protected function resolvePathsAndTagNameKey($paths, string $name, ?string $tag)
	{
		if (in_array($name, ['keys', 'tags', 'tag'])) {
			throw new \InvalidArgumentException("`{$name}` is a reserved path name");
		}

		$paths              = (array)$paths;
		$this->paths[$name] = isset($this->paths[$name]) ? array_diff($this->paths[$name], $paths) : [];
		if ($tag) {
			$this->tags[$tag]        = isset($this->tags[$name]) ? array_diff($this->tags[$tag], $paths) : [];
			$tagNameKey              = "{$tag}-{$name}";
			$this->tags[$tagNameKey] = isset($this->tags[$tagNameKey]) ? array_diff($this->tags[$tagNameKey], $paths) : [];
		}

		return [$paths, $tagNameKey ?? null];
	}
}

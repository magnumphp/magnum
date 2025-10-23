<?php

/**
 * @file
 * \Magnum\Container\Modifier
 */

namespace Magnum\Container;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Tracks all changes to a definition.
 *
 * @package Magnum\Container
 */
class Modifier
	extends Definition
{
	/**
	 * @var array List of changes
	 */
	protected $extraChanges = [];

	public function setArguments(array $arguments): static
	{
		$this->recordChange(__METHOD__, $arguments);

		return parent::setArguments($arguments);
	}

	public function setArgument($key, $value): static
	{
		$this->recordChange(__METHOD__, $key, $value);

		return parent::setArgument($key, $value);
	}

	public function addArgument($argument): static
	{
		$this->recordChange(__METHOD__, $argument);

		return parent::addArgument($argument);
	}

	public function replaceArgument($index, $argument): static
	{
		$this->recordChange(__METHOD__, $index, $argument);

		return parent::replaceArgument($index, $argument);
	}

	public function setTags(array $tags): static
	{
		$this->recordChange(__METHOD__, $tags);

		return parent::setTags($tags);
	}

	public function addTag($name, array $attributes = []): static
	{
		$this->recordChange(__METHOD__, $name, $attributes);

		return parent::addTag($name, $attributes);
	}

	public function clearTag($name): static
	{
		$this->recordChange(__METHOD__, $name);

		return parent::clearTag($name);
	}

	public function addMethodCall(string $method, array $arguments = [], bool $returnsClone = false): static
	{
		$this->recordChange(__METHOD__, $method, $arguments);

		return parent::addMethodCall($method, $arguments, $returnsClone);
	}

	public function removeMethodCall(string $method): static
	{
		$this->recordChange(__METHOD__, $method);

		return parent::removeMethodCall($method);
	}

	public function setProperties($values): static
	{
		$this->recordChange(__METHOD__, $values);

		return parent::setProperties($values);
	}

	public function setProperty($name, $value): static
	{
		$this->recordChange(__METHOD__, $name, $value);

		return parent::setProperty($name, $value);
	}

	public function setAbstract($boolean): static
	{
		$this->recordChange(__METHOD__, $boolean);

		return parent::setAbstract($boolean);
	}

	public function setSynthetic($boolean): static
	{
		$this->recordChange(__METHOD__, $boolean);

		return parent::setSynthetic($boolean);
	}

	public function setInstanceofConditionals(array $instanceof): static
	{
		$this->recordChange(__METHOD__, $instanceof);

		return parent::setInstanceofConditionals($instanceof);
	}

	public function setDeprecated(string $package, string $version, string $message): static
	{
		$this->recordChange(__METHOD__, $package, $version, $message);

		return parent::setDeprecated($package, $version, $message);
	}

	public function setBindings(array $bindings): static
	{
		$this->recordChange(__METHOD__, $bindings);

		return parent::setBindings($bindings);
	}

	/**
	 * Applies the changes to the definition
	 *
	 * @param Definition  $definition The definition to playback the changes on
	 * @param string|null $alias      The alias if used so that the class isn't overridden
	 */
	public function apply(Definition $definition, $alias = null)
	{
		foreach (array_keys($this->getChanges()) as $key) {
			switch ($key) {
				case 'deprecated':
					// ignored as we record it differently to trap the template
					break;
				case 'decorated_service':
					$definition->setDecoratedService(...$this->getDecoratedService());
					break;
				// boolean methods
				case 'public':
				case 'autoconfigured':
				case 'lazy':
				case 'shared':
					$definition->{'set' . ucfirst($key)}($this->{'is' . ucfirst($key)}());
					break;
				case 'class':
					if ($this->getClass() === $alias) {
						// we must ignore the alias in this case
						break;
					}
				// fall through
				default:
					$definition->{'set' . ucfirst($key)}($this->{'get' . ucfirst($key)}());
					break;
			}
		}

		foreach ($this->extraChanges as $change) {
			list($method, $arguments) = $change;
			$definition->$method(...$arguments);
		}
	}

	/**
	 * Records the change
	 *
	 * @param       $method
	 * @param mixed ...$args
	 */
	protected function recordChange($method, ...$args)
	{
		static $methods = [];
		if (!isset($methods[$method])) {
			list(, $cleanMethod) = explode('::', $method, 2);
			$methods[$method] = $cleanMethod;
		}

		$this->extraChanges[] = [$methods[$method], $args];
	}
}
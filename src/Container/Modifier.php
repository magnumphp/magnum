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

	public function setArguments(array $arguments)
	{
		$this->recordChange(__METHOD__, $arguments);

		return parent::setArguments($arguments);
	}

	public function setArgument($key, $value)
	{
		$this->recordChange(__METHOD__, $key, $value);

		return parent::setArgument($key, $value);
	}

	public function addArgument($argument)
	{
		$this->recordChange(__METHOD__, $argument);

		return parent::addArgument($argument);
	}

	public function replaceArgument($index, $argument)
	{
		$this->recordChange(__METHOD__, $index, $argument);

		return parent::replaceArgument($index, $argument);
	}

	public function setTags(array $tags)
	{
		$this->recordChange(__METHOD__, $tags);

		return parent::setTags($tags);
	}

	public function addTag($name, array $attributes = [])
	{
		$this->recordChange(__METHOD__, $name, $attributes);

		return parent::addTag($name, $attributes);
	}

	public function clearTag($name)
	{
		$this->recordChange(__METHOD__, $name);

		return parent::clearTag($name);
	}

	public function addMethodCall($method, array $arguments = [])
	{
		$this->recordChange(__METHOD__, $method, $arguments);

		return parent::addMethodCall($method, $arguments);
	}

	public function removeMethodCall($method)
	{
		$this->recordChange(__METHOD__, $method);

		return parent::removeMethodCall($method);
	}

	public function setProperties($values)
	{
		$this->recordChange(__METHOD__, $values);

		return parent::setProperties($values);
	}

	public function setProperty($name, $value)
	{
		$this->recordChange(__METHOD__, $name, $value);

		return parent::setProperty($name, $value);
	}

	public function setAbstract($boolean)
	{
		$this->recordChange(__METHOD__, $boolean);

		return parent::setAbstract($boolean);
	}

	public function setSynthetic($boolean)
	{
		$this->recordChange(__METHOD__, $boolean);

		return parent::setSynthetic($boolean);
	}

	public function setInstanceofConditionals(array $instanceof)
	{
		$this->recordChange(__METHOD__, $instanceof);

		return parent::setInstanceofConditionals($instanceof);
	}

	public function setDeprecated($status = true, $template = null)
	{
		$this->recordChange(__METHOD__, $status, $template);

		return parent::setDeprecated($status, $template);
	}

	public function setBindings(array $bindings)
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
					continue;
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